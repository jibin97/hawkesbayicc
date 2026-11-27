<?php
/**
 * @var array<string, list<array{value:string,label:string}>> $placements
 * @var string                                               $placeValue
 * @var string                                               $section
 * @var array                                                $sectionDef
 * @var string|null                                          $itemLabel
 * @var list<array<string,mixed>>                            $images
 * @var array<string,int>                                    $counts
 * @var string|null                                          $notice
 * @var string|null                                          $dbError
 * @var string                                               $csrf
 * @var string                                               $maxUpload
 */
$single  = $sectionDef['kind'] === 'single';
$heading = $sectionDef['label'] . ($itemLabel !== null ? ': ' . $itemLabel : '');
$total   = count($images);
$hidden  = static fn (string $place, string $csrf): string =>
    '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="place" value="' . e($place) . '">';
?>
<section class="images-admin">
    <h1>Images</h1>
    <p class="text-muted">Choose where on the website, then upload, replace, reorder or delete photos. Changes show on the site straight away.</p>

    <?php if ($notice !== null): ?>
        <p class="notice" role="status"><?= e($notice) ?></p>
    <?php endif; ?>
    <?php if ($dbError !== null): ?>
        <p class="notice notice--error" role="alert"><?= e($dbError) ?></p>
    <?php endif; ?>

    <form class="place-picker card" method="get" action="/admin/images" data-autosubmit>
        <label for="place">Where on the site</label>
        <div class="place-picker__row">
            <select id="place" name="place">
                <?php foreach ($placements as $group => $options): ?>
                    <optgroup label="<?= e($group) ?>">
                        <?php foreach ($options as $o): ?>
                            <option value="<?= e($o['value']) ?>" <?= $o['value'] === $placeValue ? 'selected' : '' ?>>
                                <?= e($o['label']) ?> (<?= (int) ($counts[$o['value']] ?? 0) ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button--outline">Show</button>
        </div>
        <p class="form__help">The number shows how many photos each place has.</p>
    </form>

    <header class="images-admin__head">
        <h2><?= e($heading) ?></h2>
        <p class="text-muted"><?= e($sectionDef['help']) ?></p>
        <p class="text-small text-muted">
            <?= $single ? 'Holds one photo: uploading again replaces it.' : 'Gallery: ' . $total . ' photo' . ($total === 1 ? '' : 's') . ', shown in this order.' ?>
        </p>
    </header>

    <?php if ($images === []): ?>
        <p class="empty">No photo here yet, so the website shows a plain placeholder. Upload one below.</p>
    <?php else: ?>
        <ol class="image-grid">
            <?php foreach ($images as $i => $img): ?>
                <li class="card image-card" id="image-<?= (int) $img['id'] ?>">
                    <a class="image-card__thumb" href="<?= e($img['path']) ?>" target="_blank" rel="noopener">
                        <img src="<?= e($img['path_sm']) ?>" alt="" width="<?= min(640, (int) $img['width']) ?>"
                             height="<?= (int) round($img['height'] * min(640, (int) $img['width']) / max(1, (int) $img['width'])) ?>" loading="lazy">
                        <span class="visually-hidden">Open full size (new tab)</span>
                    </a>
                    <p class="image-card__meta text-small text-muted">
                        <?php if (!$single): ?><strong>#<?= $i + 1 ?></strong> · <?php endif; ?>
                        <?= (int) $img['width'] ?>×<?= (int) $img['height'] ?> ·
                        <?= e(date('j M Y', strtotime((string) $img['updated_at']))) ?>
                    </p>

                    <form method="post" action="/admin/images/action" class="image-card__form">
                        <?= $hidden($placeValue, $csrf) ?>
                        <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                        <label for="alt-<?= (int) $img['id'] ?>">Alt text <span class="text-muted">(describe the photo)</span></label>
                        <input id="alt-<?= (int) $img['id'] ?>" name="alt" type="text" required maxlength="255" value="<?= e($img['alt_text']) ?>">
                        <label for="fit-<?= (int) $img['id'] ?>">Display</label>
                        <select id="fit-<?= (int) $img['id'] ?>" name="fit">
                            <option value="cover" <?= $img['fit'] === 'cover' ? 'selected' : '' ?>>Fill the frame (may crop edges)</option>
                            <option value="contain" <?= $img['fit'] === 'contain' ? 'selected' : '' ?>>Show whole photo (posters)</option>
                        </select>
                        <button type="submit" name="action" value="save" class="button--outline">Save details</button>
                    </form>

                    <div class="image-card__actions">
                        <?php if (!$single): ?>
                            <form method="post" action="/admin/images/action">
                                <?= $hidden($placeValue, $csrf) ?>
                                <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                                <button type="submit" name="action" value="up" class="button--outline" <?= $i === 0 ? 'disabled' : '' ?>
                                        aria-label="Move photo <?= $i + 1 ?> earlier">↑ Earlier</button>
                                <button type="submit" name="action" value="down" class="button--outline" <?= $i === $total - 1 ? 'disabled' : '' ?>
                                        aria-label="Move photo <?= $i + 1 ?> later">↓ Later</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="/admin/images/action">
                            <?= $hidden($placeValue, $csrf) ?>
                            <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                            <button type="submit" name="action" value="delete" class="button--danger"
                                    data-confirm="Delete this photo from the website? This cannot be undone.">Delete</button>
                        </form>
                    </div>

                    <?php if (!$single): /* single places are replaced with the upload form below */ ?>
                    <form method="post" action="/admin/images/action" enctype="multipart/form-data" class="image-card__replace">
                        <?= $hidden($placeValue, $csrf) ?>
                        <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                        <label for="replace-<?= (int) $img['id'] ?>">Replace with a new photo</label>
                        <input id="replace-<?= (int) $img['id'] ?>" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required>
                        <button type="submit" name="action" value="replace" class="button--outline">Replace</button>
                    </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <form method="post" action="/admin/images/action" enctype="multipart/form-data" class="card upload-form" id="upload">
        <h2><?= $single && $images !== [] ? 'Replace this photo' : ($single ? 'Upload a photo' : 'Add photos') ?></h2>
        <?= $hidden($placeValue, $csrf) ?>
        <input type="hidden" name="action" value="upload">
        <label for="photos"><?= $single ? 'Photo' : 'Photos' ?></label>
        <p class="form__help" id="photos-help">
            JPEG, PNG or WebP, up to <?= e($maxUpload) ?> each<?= $single ? '' : '; choose several at once (up to 20)' ?>.
            Photos are resized and converted for the web automatically.
        </p>
        <input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" required
               aria-describedby="photos-help" <?= $single ? '' : 'multiple' ?>>
        <label for="upload-alt">Alt text</label>
        <p class="form__help" id="upload-alt-help">Describe what the photo shows, e.g. "Students performing Bharatanatyam at M.A.D.E 2025".<?= $single ? '' : ' Applies to every photo in this upload; you can edit each one afterwards.' ?></p>
        <input id="upload-alt" name="alt" type="text" required maxlength="255" aria-describedby="upload-alt-help"
               value="<?= e($images[0]['alt_text'] ?? '') ?>">
        <label for="upload-fit">Display</label>
        <select id="upload-fit" name="fit">
            <option value="cover" <?= $sectionDef['fit'] === 'cover' ? 'selected' : '' ?>>Fill the frame (may crop edges)</option>
            <option value="contain" <?= $sectionDef['fit'] === 'contain' ? 'selected' : '' ?>>Show whole photo (posters)</option>
        </select>
        <button type="submit"><?= $single && $images !== [] ? 'Upload and replace' : 'Upload' ?></button>
    </form>
</section>
