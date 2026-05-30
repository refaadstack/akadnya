<?php

namespace App\Exceptions\Template;

/**
 * Exception thrown when a template is not found in the filesystem.
 */
class TemplateNotFoundException extends TemplateException
{
    public static function forSlug(string $slug): self
    {
        return new self("Template with slug '{$slug}' not found in filesystem.");
    }
}
