<?php

namespace App\Services;

use App\Models\Profile;

class MentionRenderer
{
    /**
     * Turn plain text with @handle into safe HTML; known handles become profile links with stable ids for deep links.
     *
     * @param  string  $sourceType  e.g. forum_post, lesson_comment
     */
    public function toHtml(string $text, string $sourceType, int|string $sourceId): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        preg_match_all('/@([a-zA-Z0-9_]{2,32})/', $text, $matches);
        $handles = array_unique($matches[1] ?? []);
        if ($handles === []) {
            return nl2br($text, false);
        }

        $profiles = Profile::query()
            ->whereIn('handle', $handles)
            ->get()
            ->keyBy('handle');

        $i = 0;

        $html = preg_replace_callback(
            '/@([a-zA-Z0-9_]{2,32})/',
            function (array $m) use ($sourceType, $sourceId, &$i, $profiles): string {
                $handle = $m[1];
                $anchorId = 'mt-'.$sourceType.'-'.$sourceId.'-'.$i;
                $i++;

                $profile = $profiles->get($handle);
                if (! $profile) {
                    return '<span class="mention-unknown text-zinc-500">@'.htmlspecialchars($handle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>';
                }

                $url = route('profiles.show', $profile);

                return '<a href="'.e($url).'" id="'.e($anchorId).'" class="mention-link font-medium text-emerald-400 underline decoration-emerald-600/50 underline-offset-2 hover:text-emerald-300">@'.e($handle).'</a>';
            },
            $text
        );

        return nl2br($html ?? $text, false);
    }
}
