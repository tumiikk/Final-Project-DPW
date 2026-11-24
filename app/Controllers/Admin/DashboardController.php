<?php

namespace App\Controllers\Admin;

use App\Models\GuestResponse;
use App\Models\InvitationSetting;
use App\Services\ContentRepository;
use App\Services\InvitationPublishService;

/** Halaman Ringkasan: statistik tamu, status konten, dan info yang belum diisi. */
class DashboardController
{
    /** /admin => /admin/dashboard */
    public function redirectToDashboard(): void
    {
        redirect(url('/admin/dashboard'));
    }

    public function index(): void
    {
        $data = [
            'stats' => GuestResponse::stats(),
            'content' => (new InvitationPublishService())->status(),
            'missing_info' => (new ContentRepository())->missingInfo(),
            'online' => InvitationSetting::current()['is_online'],
        ];

        if (expects_json()) {
            json_response($data);
        } else {
            html_response(view('admin.dashboard', $data));
        }
    }
}
