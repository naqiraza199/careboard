<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class ForgotPassword extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $title = 'Forgot Password';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.auth.forgot-password';

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->placeholder('Enter your email address')
                    ->extraInputAttributes(['class' => 'custom-input']),
            ])
            ->statePath('data');
    }

    public function sendPasswordResetLink(): void
    {
        $data = $this->form->getState();

        $status = Password::broker('users')->sendResetLink(
            ['email' => $data['email']]
        );

        if ($status === Password::RESET_LINK_SENT) {
            Notification::make()
                ->title('Reset Link Sent')
                ->body('We have emailed your password reset link. Please check your inbox.')
                ->success()
                ->send();

            $this->form->fill();
        } else {
            Notification::make()
                ->title('Error')
                ->body('Unable to send reset link. Please check your email address.')
                ->danger()
                ->send();
        }
    }
}
