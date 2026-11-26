<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Renders PHP templates from views/. Templates must escape their own output with e().
 */
final class View
{
    /**
     * Render a template, optionally wrapped in a layout that receives it as raw HTML in $content.
     *
     * @param array<string, mixed> $vars
     */
    public static function render(string $template, array $vars = [], ?string $layout = null): string
    {
        $html = self::capture($template, $vars);
        if ($layout === null) {
            return $html;
        }

        return self::capture($layout, ['content' => $html] + $vars);
    }

    /** @param array<string, mixed> $vars */
    private static function capture(string $template, array $vars): string
    {
        if (preg_match('#^[a-z0-9_-]+(/[a-z0-9_-]+)*$#', $template) !== 1) {
            throw new InvalidArgumentException('Invalid template name.');
        }

        $file = dirname(__DIR__) . '/views/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Template not found: {$template}");
        }

        ob_start();
        try {
            (static function (string $__file, array $__vars): void {
                extract($__vars, EXTR_SKIP);
                require $__file;
            })($file, $vars);
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
