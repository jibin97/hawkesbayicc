<?php

declare(strict_types=1);

namespace App\Forms;

use App\Config\Env;
use App\Inquiries\InquiryRepository;
use App\Mail\SmtpMailer;
use PDOException;
use RuntimeException;
use Throwable;

/**
 * Processes a public form POST: spam checks → validation → save → email alert.
 * Returns the state the page template needs to render the form again (errors) or the success panel.
 */
final class FormHandler
{
    private const HONEYPOT        = 'website';
    private const RATE_LIMIT      = 5;    // submissions…
    private const RATE_WINDOW_MIN = 60;   // …per IP per hour

    /**
     * 'after' is the email alert, to run once the response has been sent (so slow SMTP never delays the visitor).
     *
     * @param array<string, mixed> $post
     * @return array{status: 'sent'|'invalid'|'error', values: array<string,mixed>, errors: array<string,string>, message: ?string, after: ?callable}
     */
    public static function handle(string $form, array $post, string $ip): array
    {
        $schema = Schema::get($form);
        $fields = Schema::fields($form);
        $state  = ['status' => 'invalid', 'values' => [], 'errors' => [], 'message' => null, 'after' => null];

        // ---- Spam checks. Bots get a fake success so they don't learn what tripped them. ----
        $honeypot = $post[self::HONEYPOT] ?? '';
        try {
            $token = FormToken::check($post['_token'] ?? null, $form);
        } catch (RuntimeException $e) {
            error_log("Form {$form}: " . $e->getMessage());
            $state['status']  = 'error';
            $state['message'] = "Sorry, we couldn't send your form just now. Please call us on 021 1642979.";
            return $state;
        }
        if ((is_string($honeypot) && $honeypot !== '') || !is_string($honeypot) || $token === 'too_fast') {
            return ['status' => 'sent'] + $state;
        }

        [$values, $errors] = Validator::validate($fields, $post);
        $state['values'] = $values;

        if ($token === 'invalid' || $token === 'expired') {
            $state['status']  = 'error';
            $state['message'] = 'This form expired. Please check your details and send it again.';
            return $state;
        }
        if ($errors !== []) {
            $state['errors'] = $errors;
            return $state;
        }

        // ---- Save ----
        $map    = $schema['map'];
        $ipHash = FormToken::hashIp($ip);
        try {
            if (InquiryRepository::countRecentFromIp($ipHash, self::RATE_WINDOW_MIN) >= self::RATE_LIMIT) {
                $state['status']  = 'error';
                $state['message'] = "We've received several messages from you recently. Please call us on 021 1642979 instead.";
                return $state;
            }

            $id = InquiryRepository::create([
                'type'    => $form,
                'name'    => (string) $values[$map['name']],
                'email'   => (string) $values[$map['email']],
                'phone'   => isset($map['phone']) ? (string) ($values[$map['phone']] ?? '') : null,
                'subject' => ($schema['subject'])($values),
                'message' => isset($map['message']) ? (string) ($values[$map['message']] ?? '') : null,
                'details' => $values,
                'ip_hash' => $ipHash,
            ]);
        } catch (PDOException | RuntimeException $e) {
            error_log("Form {$form}: could not save inquiry: " . $e->getMessage());
            $state['status']  = 'error';
            $state['message'] = "Sorry, we couldn't send your form just now. Please try again, or call us on 021 1642979.";
            return $state;
        }

        // ---- Email alert (failure is logged and recorded, never shown to the visitor) ----
        $state['status'] = 'sent';
        $state['after']  = static fn () => self::notify($id, $form, $schema, $fields, $values);

        return $state;
    }

    /**
     * @param array<string, mixed>       $schema
     * @param list<array<string, mixed>> $fields
     * @param array<string, mixed>       $values
     */
    public static function notify(int $id, string $form, array $schema, array $fields, array $values): void
    {
        try {
            $mailer = SmtpMailer::fromEnv();
            if ($mailer === null) {
                InquiryRepository::recordEmail($id, 'Mail not configured (MAIL_HOST empty).');
                return;
            }

            $recipients = array_map('trim', explode(',', (string) Env::get('MAIL_NOTIFY_TO', '')));
            $subject    = '[HBICC website] ' . ($schema['subject'])($values);

            $lines = ['New ' . $form . ' submission from the website.', ''];
            foreach ($fields as $f) {
                $v = $values[$f['name']] ?? '';
                if (is_array($v)) {
                    $v = $v === [] ? '—' : implode(', ', $v);
                } elseif (is_bool($v)) {
                    $v = $v ? 'Yes' : 'No';
                } elseif ($v === '') {
                    $v = '—';
                }
                $lines[] = ($f['report'] ?? $f['label']) . ":\n  " . str_replace("\n", "\n  ", (string) $v);
            }
            $appUrl = rtrim((string) Env::get('APP_URL', ''), '/');
            if ($appUrl !== '') {
                $lines[] = '';
                $lines[] = 'View in admin: ' . $appUrl . '/admin/inquiry?id=' . $id;
            }

            $mailer->send($recipients, $subject, implode("\n", $lines), (string) $values[$schema['map']['email']]);
            InquiryRepository::recordEmail($id, null);
        } catch (Throwable $e) {
            error_log("Form {$form} #{$id}: email alert failed: " . $e->getMessage());
            try {
                InquiryRepository::recordEmail($id, $e->getMessage());
            } catch (Throwable) {
                // Already logged; the inquiry itself is saved.
            }
        }
    }
}
