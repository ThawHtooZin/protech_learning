<?php

namespace App\Services;

use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownRenderer
{
    public function toHtml(string $markdown): string
    {
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        return (new GithubFlavoredMarkdownConverter($config))
            ->convert($markdown)
            ->getContent();
    }
}
