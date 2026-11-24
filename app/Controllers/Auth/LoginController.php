<?php

namespace App\Controllers\Auth;

use App\Core\Auth;
use App\Core\ValidationException;
use App\Core\Validator;

/** Halaman login, proses login, dan logout admin. */
class LoginController
{
    public function show(): void
    {
        html_response(view('auth.login'));
    }

    /** Login dengan email ATAU username + password. */
    public function login(): void
    {
        $data = Validator::validate(request_input(), [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ]);

        // Login berisi "@" yang valid => dicari sebagai email, selain itu sebagai username
        $login = mb_strtolower(trim($data['login']));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt($field, $login, $data['password'])) {
            throw ValidationException::withMessages(['login' => 'Email/username atau password salah.']);
        }

        // Kembali ke halaman admin yang tadi ingin dibuka, atau ke Ringkasan
        $to = $_SESSION['url_intended'] ?? url('/admin/dashboard');
        unset($_SESSION['url_intended']);
        redirect($to);
    }

    public function logout(): void
    {
        Auth::logout();
        redirect(url('/admin/login'));
    }
}
