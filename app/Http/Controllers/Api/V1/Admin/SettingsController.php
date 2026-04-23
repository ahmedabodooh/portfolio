<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\SiteSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct(private readonly SiteSettingService $settings) {}

    public function index()
    {
        return response()->json($this->settings->allGrouped());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings'   => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        $this->settings->bulkUpdate($data['settings']);
        return response()->json(['ok' => true]);
    }

    public function upload(Request $request, string $key)
    {
        // Audio settings (background music, etc.) accept audio formats + larger size.
        // Everything else stays on the image/pdf allowlist.
        $isAudio = str_contains($key, 'music') || str_contains($key, 'audio') || str_contains($key, 'sound');
        $request->validate([
            'file' => $isAudio
                ? ['required', 'file', 'max:20480', 'mimes:mp3,wav,ogg,oga,m4a,weba,webm']
                : ['required', 'file', 'max:8192',  'mimes:jpg,jpeg,png,webp,gif,svg,pdf'],
        ]);

        $existing = SiteSetting::query()->where('key', $key)->first();
        if ($existing && is_string($existing->value) && str_starts_with($existing->value, '/storage/')) {
            $oldPath = ltrim(substr($existing->value, strlen('/storage/')), '/');
            Storage::disk('public')->delete($oldPath);
        }

        $stored = $request->file('file')->store('settings', 'public');
        $url = '/storage/' . $stored;

        $row = $this->settings->set($key, $url);

        return response()->json([
            'ok'    => true,
            'key'   => $key,
            'url'   => $url,
            'value' => $url,
            'id'    => $row->id,
        ]);
    }

    public function clearFile(Request $request, string $key)
    {
        $existing = SiteSetting::query()->where('key', $key)->first();
        if ($existing && is_string($existing->value) && str_starts_with($existing->value, '/storage/')) {
            $oldPath = ltrim(substr($existing->value, strlen('/storage/')), '/');
            Storage::disk('public')->delete($oldPath);
        }
        $this->settings->set($key, null);
        return response()->json(['ok' => true]);
    }

    public function dashboard()
    {
        return response()->json([
            'profile'  => $this->settings->profile(),
            'branding' => $this->settings->branding(),
            'counts'   => [
                'projects'       => \App\Models\Project::count(),
                'blog_posts'     => \App\Models\BlogPost::count(),
                'skills'         => \App\Models\Skill::count(),
                'experiences'    => \App\Models\Experience::count(),
                'certifications' => \App\Models\Certification::count(),
                'messages_unread'=> \App\Models\ContactMessage::where('is_read', false)->count(),
                'messages_total' => \App\Models\ContactMessage::count(),
            ],
        ]);
    }
}
