<?php

namespace App\Support;

final class HtmlSanitizer
{
    private const ALLOWED = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><blockquote><span>';

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);
        if ($html === '' || $html === '<p><br></p>' || $html === '<p></p>') {
            return null;
        }

        $clean = strip_tags($html, self::ALLOWED);
        $clean = preg_replace('/\son\w+\s*=\s*([\'"]).*?\1/i', '', $clean) ?? $clean;
        $clean = preg_replace('/javascript\s*:/i', '', $clean) ?? $clean;

        return $clean;
    }
}
