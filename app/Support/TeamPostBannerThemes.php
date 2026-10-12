<?php

namespace App\Support;

final class TeamPostBannerThemes
{
    /**
     * Curated Teams-style announcement banner palettes.
     *
     * @var array<string, array{bg: string, text: string, label: string}>
     */
    public const PRESETS = [
        'peach' => ['bg' => '#F5D5C8', 'text' => '#7A2E2E', 'label' => 'Peach'],
        'sky' => ['bg' => '#C7E4F5', 'text' => '#0C4A6E', 'label' => 'Sky'],
        'mint' => ['bg' => '#C8EDE0', 'text' => '#14532D', 'label' => 'Mint'],
        'lavender' => ['bg' => '#E0D4F7', 'text' => '#4C1D95', 'label' => 'Lavender'],
        'sand' => ['bg' => '#F5E6C8', 'text' => '#78350F', 'label' => 'Sand'],
        'rose' => ['bg' => '#FBCFE8', 'text' => '#9D174D', 'label' => 'Rose'],
        'slate' => ['bg' => '#E2E8F0', 'text' => '#1E293B', 'label' => 'Slate'],
        'emerald' => ['bg' => '#A7F3D0', 'text' => '#064E3B', 'label' => 'Emerald'],
    ];

    /**
     * @return array{bg: string, text: string}|null
     */
    public static function resolve(?string $themeKey, ?string $seed = null): ?array
    {
        if ($themeKey === null || $themeKey === '' || $themeKey === 'none') {
            return null;
        }

        if ($themeKey === 'auto') {
            $keys = array_keys(self::PRESETS);
            $index = abs(crc32((string) $seed)) % count($keys);

            return self::colors($keys[$index]);
        }

        return self::colors($themeKey);
    }

    /**
     * @return array{bg: string, text: string}|null
     */
    public static function colors(string $key): ?array
    {
        $preset = self::PRESETS[$key] ?? null;
        if (! $preset) {
            return null;
        }

        return [
            'bg' => $preset['bg'],
            'text' => $preset['text'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::PRESETS);
    }
}
