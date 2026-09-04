<?php

namespace App\Services;

class EmailTemplate
{
    /**
     * Wrap raw HTML body content in the standard Akadnya branded email layout.
     * Includes the Akadnya logo at the top and a footer with support info.
     *
     * The logo is loaded from the public/images/logo.png asset (absolute URL).
     */
    public static function wrap(string $title, string $body): string
    {
        $logoUrl = url('images/logo.png').'?v=1';
        $appName = e(config('app.name', 'Akadnya.com'));
        $appUrl = e(url('/'));
        $supportEmail = 'support@akadnya.com';
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
</head>
<body style="margin:0;padding:0;background-color:#F6F0E2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#141F1A;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F6F0E2;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background-color:#FFFFFF;border-radius:12px;overflow:hidden;border:1px solid #E8DFC8;">
                    <tr>
                        <td align="center" style="padding:28px 24px 16px 24px;background-color:#FFFFFF;border-bottom:1px solid #F0E8D6;">
                            <a href="{$appUrl}" style="text-decoration:none;display:inline-block;">
                                <img src="{$logoUrl}" alt="{$appName}" width="200" style="display:block;max-width:200px;height:auto;border:0;" />
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 24px 32px;background-color:#FFFFFF;">
                            {$body}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px;background-color:#F6F0E2;border-top:1px solid #E8DFC8;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size:12px;line-height:18px;color:#7A6B4A;text-align:center;">
                                        &copy; {$year} {$appName}. Semua hak dilindungi.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top:8px;font-size:12px;line-height:18px;color:#7A6B4A;text-align:center;">
                                        Butuh bantuan? <a href="mailto:{$supportEmail}" style="color:#AD7F35;text-decoration:none;">{$supportEmail}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * A button styled with brand colors. Returns inline-block anchor.
     */
    public static function button(string $url, string $label, string $variant = 'primary'): string
    {
        $url = e($url);
        $label = e($label);

        $styles = match ($variant) {
            'secondary' => 'background-color:#FFFFFF;color:#141F1A;border:1px solid #AD7F35;',
            default => 'background-color:#AD7F35;color:#FFFFFF;border:1px solid #AD7F35;',
        };

        return <<<HTML
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr>
        <td align="center" style="border-radius:8px;{$styles}">
            <a href="{$url}" target="_blank" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:bold;color:inherit;text-decoration:none;border-radius:8px;">
                {$label}
            </a>
        </td>
    </tr>
</table>
HTML;
    }
}
