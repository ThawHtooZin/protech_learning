<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use App\Notifications\UserMentionedNotification;
use Illuminate\Support\Collection;

class MentionService
{
    /** @return Collection<int, string> handles without @ */
    public function extractHandles(string $text): Collection
    {
        preg_match_all('/@([a-zA-Z0-9_]{2,32})/', $text, $m);

        return collect($m[1] ?? [])->unique()->values();
    }

    /**
     * @param  array<string, mixed>  $meta  Expects: message, base_url (no fragment), source_type, source_id
     */
    public function notifyMentionedUsers(User $actor, string $body, array $meta): void
    {
        $handles = $this->extractHandles($body);
        if ($handles->isEmpty()) {
            return;
        }

        $profiles = Profile::query()->whereIn('handle', $handles->all())->with('user')->get();

        preg_match_all('/@([a-zA-Z0-9_]{2,32})/', $body, $orderMatches);
        $occurrenceOrder = $orderMatches[1] ?? [];

        $baseUrl = $meta['base_url'] ?? null;
        if ($baseUrl === null && isset($meta['url'])) {
            $baseUrl = explode('#', (string) $meta['url'], 2)[0];
        }
        if ($baseUrl === null) {
            $baseUrl = '/';
        }

        $sourceType = (string) ($meta['source_type'] ?? 'mention');
        $sourceId = $meta['source_id'] ?? 0;

        foreach ($profiles as $profile) {
            $user = $profile->user;
            if (! $user || $user->id === $actor->id) {
                continue;
            }

            $mentionIndex = null;
            foreach ($occurrenceOrder as $idx => $h) {
                if ($h === $profile->handle) {
                    $mentionIndex = $idx;
                    break;
                }
            }
            if ($mentionIndex === null) {
                continue;
            }

            $fragment = 'mt-'.$sourceType.'-'.$sourceId.'-'.$mentionIndex;
            $url = rtrim($baseUrl, '/').'#'.$fragment;

            $user->notify(new UserMentionedNotification($actor, array_merge($meta, [
                'message' => $meta['message'] ?? __('You were mentioned.'),
                'url' => $url,
            ])));
        }
    }
}
