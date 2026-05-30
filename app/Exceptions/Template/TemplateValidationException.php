<?php

namespace App\Exceptions\Template;

/**
 * Exception thrown when template ZIP structure is incomplete.
 */
class TemplateValidationException extends TemplateException
{
    public static function missingFiles(array $files): self
    {
        $fileList = implode(', ', $files);

        return new self("Template validation failed: missing files ({$fileList}).");
    }

    public static function invalidFileType(string $file, string $expectedType): self
    {
        return new self("File '{$file}' is not a valid {$expectedType} file.");
    }
}
