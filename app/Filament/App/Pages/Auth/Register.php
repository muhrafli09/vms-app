<?php

namespace App\Filament\App\Pages\Auth;

use Filament\Forms;
use Livewire\Attributes\Url;
use App\Models\UserInvitation;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Auth\Events\Registered;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Register extends BaseRegister
{

    #[Url]
    public $token = '';

    public ?UserInvitation $invitation = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->invitation = UserInvitation::where('code', $this->token)->firstOrFail();

        $this->form->fill([
            'email' => $this->invitation->email,
        ]);
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $user = $this->getUserModel()::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        
        // Auto-create employee record for new user
        \App\Models\Employee::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'first_name' => explode(' ', $user->name)[0],
            'last_name' => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: null,
            'department_id' => $data['department_id'] ?? null,
            'designation_id' => $data['designation_id'] ?? null,
        ]);

        $this->invitation->delete();

        event(new Registered($user));

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }
    
    protected function getRedirectUrl(): string
    {
        return Filament::getUrl();
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getDepartmentFormComponent(),
                        $this->getDesignationFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return Forms\Components\TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel())
            ->readOnly();
    }
    
    protected function getDepartmentFormComponent(): Component
    {
        return Forms\Components\Select::make('department_id')
            ->label('Department')
            ->options(\App\Models\Department::pluck('name', 'id'))
            ->searchable()
            ->preload()
            ->native(false);
    }
    
    protected function getDesignationFormComponent(): Component
    {
        return Forms\Components\Select::make('designation_id')
            ->label('Designation')
            ->options(\App\Models\Designation::pluck('name', 'id'))
            ->searchable()
            ->preload()
            ->native(false);
    }
}
