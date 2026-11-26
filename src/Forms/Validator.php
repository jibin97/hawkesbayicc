<?php

declare(strict_types=1);

namespace App\Forms;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Validates and normalises submitted values against a Schema. Rejects rather than repairs.
 */
final class Validator
{
    /**
     * Error messages for a field: shared by server-side validation and the browser-side script
     * (rendered as data attributes), so visitors see the same wording either way.
     *
     * @param array<string, mixed> $f
     * @return array{required: string, invalid: string, future: string}
     */
    public static function messages(array $f): array
    {
        // 'noun' reads naturally after "Please enter/choose …"; otherwise fall back to the label.
        $label = $f['noun'] ?? lcfirst(rtrim(preg_replace('/\s*\(optional\)$/i', '', $f['label']) ?? $f['label'], '?.'));

        $required = match ($f['type']) {
            'select', 'radio' => "Please choose {$label}.",
            'checkboxes'      => "Please choose at least one option for {$label}.",
            'checkbox'        => 'Please tick this box to continue.',
            default           => "Please enter {$label}.",
        };
        $invalid = match ($f['type']) {
            'email'  => 'Please enter a valid email address, like name@example.com.',
            'tel'    => 'Please enter a valid phone number, like 021 123 4567.',
            'date'   => 'Please enter a valid date.',
            'number' => 'Please enter a whole number' . (isset($f['max_num']) ? " up to {$f['max_num']}" : '') . '.',
            'select', 'radio', 'checkboxes' => 'Please choose from the options listed.',
            default  => isset($f['max']) ? "Please keep {$label} under {$f['max']} characters." : 'Please check this field.',
        };

        return ['required' => $required, 'invalid' => $invalid, 'future' => 'Please choose a date in the future.'];
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @param array<string, mixed>       $input  usually $_POST
     * @return array{0: array<string, mixed>, 1: array<string, string>} [values, errors keyed by field name]
     */
    public static function validate(array $fields, array $input): array
    {
        $values = [];
        $errors = [];

        foreach ($fields as $f) {
            $name     = $f['name'];
            $msg      = self::messages($f);
            $required = (bool) ($f['required'] ?? false);
            $raw      = $input[$name] ?? null;

            // ---- Multi-value checkboxes ----
            if ($f['type'] === 'checkboxes') {
                $chosen = is_array($raw) ? $raw : [];
                $clean  = [];
                foreach ($chosen as $item) {
                    if (!is_string($item) || !in_array($item, $f['options'], true)) {
                        $errors[$name] = $msg['invalid'];
                        $clean = [];
                        break;
                    }
                    $clean[] = $item;
                }
                if (!isset($errors[$name]) && $required && $clean === []) {
                    $errors[$name] = $msg['required'];
                }
                $values[$name] = array_values(array_unique($clean));
                continue;
            }

            // ---- Single checkbox (must be ticked if required) ----
            if ($f['type'] === 'checkbox') {
                $ticked = $raw === '1';
                if ($required && !$ticked) {
                    $errors[$name] = $msg['required'];
                }
                $values[$name] = $ticked;
                continue;
            }

            // ---- Everything else is a single string ----
            if ($raw !== null && !is_string($raw)) {
                $errors[$name] = $msg['invalid'];
                $values[$name] = '';
                continue;
            }
            $value = trim((string) $raw);
            if ($f['type'] !== 'textarea') {
                $value = (string) preg_replace('/\s+/u', ' ', $value);
            }
            $values[$name] = $value;

            if ($value === '') {
                if ($required) {
                    $errors[$name] = $msg['required'];
                }
                continue;
            }

            $controlChars = $f['type'] === 'textarea' ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/' : '/[\x00-\x1F\x7F]/';
            if (preg_match('//u', $value) !== 1 || preg_match($controlChars, $value) === 1) {
                $errors[$name] = 'Please check this field for unusual characters.';
                continue;
            }
            if (isset($f['max']) && mb_strlen($value) > $f['max']) {
                $errors[$name] = "Please keep this under {$f['max']} characters.";
                continue;
            }

            switch ($f['type']) {
                case 'email':
                    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                        $errors[$name] = $msg['invalid'];
                    } else {
                        $values[$name] = strtolower($value);
                    }
                    break;

                case 'tel':
                    $digits = preg_replace('/\D/', '', $value) ?? '';
                    if (preg_match('/^[0-9+()\-\s.]+$/', $value) !== 1 || strlen($digits) < 7 || strlen($digits) > 15) {
                        $errors[$name] = $msg['invalid'];
                    }
                    break;

                case 'select':
                case 'radio':
                    if (!in_array($value, $f['options'], true)) {
                        $errors[$name] = $msg['invalid'];
                    }
                    break;

                case 'number':
                    if (preg_match('/^\d+$/', $value) !== 1
                        || (int) $value < ($f['min'] ?? 0)
                        || (int) $value > ($f['max_num'] ?? PHP_INT_MAX)) {
                        $errors[$name] = $msg['invalid'];
                    } else {
                        $values[$name] = (string) (int) $value;
                    }
                    break;

                case 'date':
                    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                    if ($date === false || $date->format('Y-m-d') !== $value) {
                        $errors[$name] = $msg['invalid'];
                    } elseif (!empty($f['future']) && $value < self::today()) {
                        $errors[$name] = $msg['future'];
                    }
                    break;
            }
        }

        return [$values, $errors];
    }

    /** Today in New Zealand, as Y-m-d. */
    public static function today(): string
    {
        return (new DateTimeImmutable('today', new DateTimeZone('Pacific/Auckland')))->format('Y-m-d');
    }
}
