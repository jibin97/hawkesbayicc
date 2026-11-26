<?php
/**
 * Schema-driven public form. Works without JavaScript (native validation + server-side checks);
 * site.js adds inline validation and a "sending" state.
 *
 * @var string     $form   enrolment|booking|contact
 * @var string     $action Page path + #anchor to post back to
 * @var array|null $state  From FormHandler / route: status, values, errors, message
 * @var array      $site
 */
use App\Forms\FormToken;
use App\Forms\Schema;
use App\Forms\Validator;
use App\View;

$schema = Schema::get($form);
$state ??= null;
$values = $state['values'] ?? [];
$errors = $state['errors'] ?? [];
$prefix = 'f-' . $form . '-';

if (($state['status'] ?? null) === 'sent'): ?>
    <div class="card form-success" role="status" tabindex="-1" data-form-success>
        <span class="form-success__icon" aria-hidden="true">✓</span>
        <h3><?= e($schema['success']['title']) ?></h3>
        <p class="text-muted"><?= e($schema['success']['text']) ?></p>
    </div>
<?php
    return;
endif;

try {
    $token = FormToken::issue($form);
} catch (Throwable $e) {
    error_log("Form {$form}: cannot issue token: " . $e->getMessage());
    echo View::partial('public/partials/form-slot', [
        'site'    => $site,
        'heading' => 'Online form unavailable',
        'text'    => "Please call or email us instead and we'll get back to you.",
    ]);
    return;
}

/** Describe-by ids for a field. */
$describedBy = static function (array $f) use ($prefix, $errors): string {
    $ids = [];
    if (!empty($f['help'])) {
        $ids[] = $prefix . $f['name'] . '-help';
    }
    if (isset($errors[$f['name']])) {
        $ids[] = $prefix . $f['name'] . '-error';
    }
    return $ids === [] ? '' : ' aria-describedby="' . e(implode(' ', $ids)) . '"';
};

/** Label text, with "(optional)" added to optional fields that don't already say so. */
$labelText = static function (array $f): string {
    $label = $f['label'];
    if (empty($f['required']) && $f['type'] !== 'checkbox' && stripos($label, '(optional)') === false) {
        $label .= ' (optional)';
    }
    return $label;
};
?>
<form class="card form" method="post" action="<?= e($action) ?>" data-validate>
    <?php if (!empty($state['message'])): ?>
        <div class="alert alert--danger form__alert" role="alert"><p><?= e($state['message']) ?></p></div>
    <?php endif; ?>

    <div class="form__summary alert alert--danger" role="alert" tabindex="-1" data-error-summary <?= $errors === [] ? 'hidden' : '' ?>>
        <h3 class="form__summary-title">Please fix <?= count($errors) === 1 ? 'this problem' : 'these ' . count($errors) . ' problems' ?>:</h3>
        <ul>
            <?php foreach (Schema::fields($form) as $f): if (!isset($errors[$f['name']])) continue;
                $target = in_array($f['type'], ['radio', 'checkboxes'], true) ? $prefix . $f['name'] . '-0' : $prefix . $f['name']; ?>
                <li><a href="#<?= e($target) ?>"><?= e($errors[$f['name']]) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <input type="hidden" name="_token" value="<?= e($token) ?>">
    <div class="form__hp" aria-hidden="true">
        <label for="<?= e($prefix) ?>website">Leave this field empty</label>
        <input type="text" id="<?= e($prefix) ?>website" name="website" value="" tabindex="-1" autocomplete="off">
    </div>

    <?php foreach ($schema['sections'] as $section): ?>
        <fieldset class="form__section">
            <?php if (!empty($section['legend'])): ?>
                <legend class="form__legend"><?= e($section['legend']) ?></legend>
            <?php endif; ?>
            <?php if (!empty($section['help'])): ?>
                <p class="field__help"><?= e($section['help']) ?></p>
            <?php endif; ?>

            <div class="form__grid">
            <?php foreach ($section['fields'] as $f):
                $name     = $f['name'];
                $id       = $prefix . $name;
                $value    = $values[$name] ?? ($f['type'] === 'checkboxes' ? [] : '');
                $hasError = isset($errors[$name]);
                $msgs     = Validator::messages($f);
                $req      = !empty($f['required']) ? ' required' : '';
                $invalid  = $hasError ? ' aria-invalid="true"' : '';
                $classes  = 'field' . (($f['width'] ?? '') === 'half' ? ' field--half' : '') . ($hasError ? ' field--error' : '');
                $data     = ' data-field data-msg-required="' . e($msgs['required']) . '" data-msg-invalid="' . e($msgs['invalid']) . '" data-msg-future="' . e($msgs['future']) . '"';
            ?>
                <?php if ($f['type'] === 'radio' || $f['type'] === 'checkboxes'): ?>
                    <fieldset class="<?= $classes ?> field--choices"<?= $data ?><?= $describedBy($f) ?>>
                        <legend class="label"><?= e($labelText($f)) ?></legend>
                        <?php if (!empty($f['help'])): ?><p class="field__help" id="<?= e($id) ?>-help"><?= e($f['help']) ?></p><?php endif; ?>
                        <p class="field__error" id="<?= e($id) ?>-error" <?= $hasError ? '' : 'hidden' ?>><span class="visually-hidden">Error: </span><span data-error-text><?= $hasError ? e($errors[$name]) : '' ?></span></p>
                        <div class="choices<?= count($f['options']) > 3 ? ' choices--grid' : '' ?>">
                            <?php foreach ($f['options'] as $i => $opt):
                                $checked = $f['type'] === 'radio' ? $value === $opt : in_array($opt, (array) $value, true); ?>
                                <label class="choice" for="<?= e($id) ?>-<?= $i ?>">
                                    <input type="<?= $f['type'] === 'radio' ? 'radio' : 'checkbox' ?>" id="<?= e($id) ?>-<?= $i ?>"
                                           name="<?= e($name) ?><?= $f['type'] === 'checkboxes' ? '[]' : '' ?>" value="<?= e($opt) ?>"
                                           <?= $checked ? 'checked' : '' ?><?= $f['type'] === 'radio' ? $req : '' ?><?= $invalid ?>>
                                    <span><?= e($opt) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                <?php elseif ($f['type'] === 'checkbox'): ?>
                    <div class="<?= $classes ?>"<?= $data ?>>
                        <p class="field__error" id="<?= e($id) ?>-error" <?= $hasError ? '' : 'hidden' ?>><span class="visually-hidden">Error: </span><span data-error-text><?= $hasError ? e($errors[$name]) : '' ?></span></p>
                        <label class="choice choice--consent" for="<?= e($id) ?>">
                            <input type="checkbox" id="<?= e($id) ?>" name="<?= e($name) ?>" value="1" <?= $value === true ? 'checked' : '' ?><?= $req ?><?= $invalid ?><?= $describedBy($f) ?>>
                            <span><?= e($f['label']) ?></span>
                        </label>
                    </div>

                <?php else: ?>
                    <div class="<?= $classes ?>"<?= $data ?>>
                        <label for="<?= e($id) ?>"><?= e($labelText($f)) ?></label>
                        <?php if (!empty($f['help'])): ?><p class="field__help" id="<?= e($id) ?>-help"><?= e($f['help']) ?></p><?php endif; ?>
                        <p class="field__error" id="<?= e($id) ?>-error" <?= $hasError ? '' : 'hidden' ?>><span class="visually-hidden">Error: </span><span data-error-text><?= $hasError ? e($errors[$name]) : '' ?></span></p>
                        <?php
                        $common = 'id="' . e($id) . '" name="' . e($name) . '"' . $req . $invalid . $describedBy($f)
                            . (!empty($f['autocomplete']) ? ' autocomplete="' . e($f['autocomplete']) . '"' : '')
                            . (!empty($f['inputmode']) ? ' inputmode="' . e($f['inputmode']) . '"' : '');
                        ?>
                        <?php if ($f['type'] === 'select'): ?>
                            <select <?= $common ?>>
                                <option value="">Choose…</option>
                                <?php foreach ($f['options'] as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= $value === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($f['type'] === 'textarea'): ?>
                            <textarea <?= $common ?> rows="5" maxlength="<?= (int) ($f['max'] ?? 2000) ?>"><?= e((string) $value) ?></textarea>
                        <?php else:
                            $extra = match ($f['type']) {
                                'tel'    => ' pattern="[0-9+()\-\s.]{7,20}"',
                                'number' => ' min="' . (int) ($f['min'] ?? 0) . '"' . (isset($f['max_num']) ? ' max="' . (int) $f['max_num'] . '"' : ''),
                                'date'   => !empty($f['future']) ? ' min="' . e(Validator::today()) . '"' : '',
                                default  => '',
                            };
                            if (isset($f['max']) && in_array($f['type'], ['text', 'email', 'tel'], true)) {
                                $extra .= ' maxlength="' . (int) $f['max'] . '"';
                            } ?>
                            <input type="<?= e($f['type']) ?>" <?= $common ?> value="<?= e((string) $value) ?>"<?= $extra ?>>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
        </fieldset>
    <?php endforeach; ?>

    <div class="form__actions">
        <button type="submit" data-submit><?= e($schema['submit']) ?></button>
        <p class="text-muted text-small">We'll only use your details to respond to this enquiry.</p>
    </div>
</form>
