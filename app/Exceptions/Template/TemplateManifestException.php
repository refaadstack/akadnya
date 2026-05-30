<?php

namespace App\Exceptions\Template;

/**
 * Exception thrown when template.json is invalid or missing.
 */
class TemplateManifestException extends TemplateException
{
    public static function notFound(): self
    {
        return new self('template.json not found in ZIP package.');
    }

    public static function missingRequiredFields(array $fields): self
    {
        $fieldList = implode(', ', $fields);

        return new self("template.json is invalid: required fields missing ({$fieldList}).");
    }

    public static function invalidJson(string $error): self
    {
        return new self("template.json contains invalid JSON: {$error}");
    }
}
