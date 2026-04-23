<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\CertificationService;
use App\Services\ClientService;
use App\Services\ExperienceService;
use App\Services\SiteSettingService;
use App\Services\SkillService;

class SiteController extends Controller
{
    public function __construct(
        private readonly SiteSettingService $settings,
        private readonly ExperienceService $experiences,
        private readonly SkillService $skills,
        private readonly CertificationService $certifications,
        private readonly ClientService $clients,
    ) {
    }

    public function index()
    {
        return response()->json([
            'name'      => 'Ahmed Abo Dooh — Portfolio API',
            'version'   => 'v1',
            'endpoints' => [
                'public' => [
                    'GET  /api/v1/site/profile',
                    'GET  /api/v1/site/branding',
                    'GET  /api/v1/site/experiences',
                    'GET  /api/v1/site/skills',
                    'GET  /api/v1/site/certifications',
                    'GET  /api/v1/site/clients',
                    'GET  /api/v1/projects',
                    'GET  /api/v1/projects/{slug}',
                    'GET  /api/v1/blog',
                    'GET  /api/v1/blog/{slug}',
                    'POST /api/v1/contact',
                ],
                'admin' => [
                    'POST /api/v1/admin/auth/login',
                    'POST /api/v1/admin/auth/logout',
                    'GET  /api/v1/admin/auth/me',
                    'CRUD /api/v1/admin/projects',
                    'CRUD /api/v1/admin/blog',
                    'CRUD /api/v1/admin/skills',
                    'CRUD /api/v1/admin/experiences',
                    'CRUD /api/v1/admin/certifications',
                    'CRUD /api/v1/admin/clients',
                    'GET  /api/v1/admin/messages',
                    'GET  /api/v1/admin/settings',
                    'PUT  /api/v1/admin/settings',
                ],
            ],
        ]);
    }

    public function profile()
    {
        return response()->json($this->settings->profile());
    }

    public function branding()
    {
        return response()->json($this->settings->branding());
    }

    public function experiences()
    {
        return response()->json($this->experiences->listPublished());
    }

    public function skills()
    {
        return response()->json($this->skills->groupedPublished());
    }

    public function certifications()
    {
        return response()->json($this->certifications->listPublished());
    }

    public function clients()
    {
        return response()->json($this->clients->listPublished());
    }
}
