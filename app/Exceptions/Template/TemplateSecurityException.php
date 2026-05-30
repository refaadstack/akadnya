<?php

namespace App\Exceptions\Template;

/**
 * Exception thrown when security issues are detected (e.g., path traversal).
 */
class TemplateSecurityException extends TemplateException
{
    public static function pathTraversalDetected(string $path): self
    {
        return new self("ZIP contains unsafe path: {$path}");
    }

    public static function unsafeFileDetected(string $file): self
    {
        return new self("ZIP contains potentially unsafe file: {$file}");
    }
}
