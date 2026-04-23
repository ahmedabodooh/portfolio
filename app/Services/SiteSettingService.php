<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettingService
{
    private const PROFILE_KEYS = [
        'owner_name', 'owner_role', 'owner_tagline', 'owner_location',
        'owner_email', 'owner_github', 'owner_linkedin',
        'available_status', 'owner_photo', 'resume_file',
        'background_music',
    ];

    private const BRANDING_KEYS = [
        'site_title', 'site_description', 'primary_color',
        'accent_color', 'logo_url', 'favicon_url', 'og_image',
    ];

    public function profile(): array
    {
        return collect(self::PROFILE_KEYS)
            ->mapWithKeys(fn ($k) => [$k => SiteSetting::get($k)])
            ->toArray();
    }

    public function branding(): array
    {
        return collect(self::BRANDING_KEYS)
            ->mapWithKeys(fn ($k) => [$k => SiteSetting::get($k)])
            ->toArray();
    }

    public function allGrouped(): array
    {
        return SiteSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(fn ($rows) => $rows->map(fn ($r) => [
                'id'    => $r->id,
                'key'   => $r->key,
                'value' => $r->value,
                'type'  => $r->type,
                'group' => $r->group,
            ])->values())
            ->toArray();
    }

    public function bulkUpdate(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_scalar($value) || is_null($value) ? (string) $value : json_encode($value)]
            );
        }
        Cache::forget('site_settings.all');
    }

    public function set(string $key, mixed $value, ?string $type = null, ?string $group = null): SiteSetting
    {
        $row = SiteSetting::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => is_scalar($value) || is_null($value) ? (string) $value : json_encode($value),
                'type'  => $type,
                'group' => $group,
            ], fn ($v) => ! is_null($v))
        );
        Cache::forget('site_settings.all');
        return $row;
    }
}
