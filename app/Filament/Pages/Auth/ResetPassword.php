<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class ResetPassword extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $title = 'Reset Password';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.auth.reset-password';

    public ?array $data = [];
    public ?string $token = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('token')
                    ->default(fn () => request()->query('token'))
                    ->required(),
                Hidden::make('email')
                    ->default(fn () => request()->query('email'))
                    ->required(),
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->confirmed()
                    ->autofocus()
                    ->extraInputAttributes(['class' => 'custom-input']),
                TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->required()
                    ->extraInputAttributes(['class' => 'custom-input']),
            ])
            ->statePath('data');
    }

    public function resetPassword(): void
    {
        $data = $this->form->getState();

        $status = Password::broker('users')->reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Notification::make()
                ->title('Password Reset Successful')
                ->body('Your password has been reset successfully. You can now log in with your new password.')
                ->success()
                ->send();

            redirect()->route('filament.admin.auth.login');
        } else {
            Notification::make()
                ->title('Error')
                ->body('Invalid or expired reset token. Please request a new password reset link.')
                ->danger()
                ->send();
        }
    }
}
