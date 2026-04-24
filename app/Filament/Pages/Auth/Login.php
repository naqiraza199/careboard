<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function getForgotPasswordUrl(): ?string
    {
        return route('filament.admin.pages.forgot-password');
    }
}
