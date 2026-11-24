<?php

namespace App\Controllers\Admin;

use App\Core\Validator;
use App\Models\InvitationSetting;

class SettingController
{
    /** Menyalakan/mematikan akses publik ke undangan (tanpa menghapus apa pun). */
    public function online(): void
    {
        $data = Validator::validate(request_input(), ['online' => ['required', 'boolean']]);
        InvitationSetting::setOnline((bool) $data['online']);

        json_response(['online' => (bool) $data['online']]);
    }
}
