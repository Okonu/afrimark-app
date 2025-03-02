<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

class ContactPersonDetails extends Register
{
    protected static string $view = 'filament.client.pages.auth.contact-person-details';

    public ?array $data = [];

    protected ?array $debtorData = null;

    public function mount(): void
    {
        $this->debtorData = Session::get('debtor_registration');

        if (auth()->check()) {
            $user = auth()->user();

            if ($user->businesses()->exists()) {
                $this->redirect(route('filament.client.pages.dashboard'));
                return;
            }

            $this->form->fill([
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(
                        'users',
                        'email',
                        ignorable: fn () => Auth::check() ? Auth::user() : null
                    )
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn () => !Auth::check())
                    ->minLength(8)
                    ->dehydrated(fn ($state) => filled($state))
                    ->hidden(fn () => Auth::check())
                    ->columnSpanFull(),

                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->required(fn () => !Auth::check())
                    ->minLength(8)
                    ->same('password')
                    ->dehydrated(false)
                    ->hidden(fn () => Auth::check())
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();

        if (Auth::check()) {
            $user = Auth::user();
            $user->name = $data['name'];
            $user->email = $data['email'];

            if (isset($data['password']) && $data['password']) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            Auth::login($user);
        }

        return $this->redirect($this->getRedirectUrl());
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.client.auth.business-information');
    }

    public function getLoginUrl(): string
    {
        return route('filament.client.auth.login');
    }

    protected function hasFullWidthFormContainer(): bool
    {
        return true;
    }
}
