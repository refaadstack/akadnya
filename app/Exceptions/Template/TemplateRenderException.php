<?php

namespace App\Exceptions\Template;

/**
 * Exception thrown when Blade rendering fails.
 */
class TemplateRenderException extends TemplateException
{
    public static function forSection(string $section, string $error): self
    {
        return new self("Failed to render section '{$section}': {$error}");
    }

    public static function forOrnament(string $ornament, string $error): self
    {
        return new self("Failed to render ornament '{$ornament}': {$error}");
    }
}
