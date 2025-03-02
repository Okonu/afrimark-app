<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Services\Business\VerificationService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

class BusinessInformation extends Register
{
    protected static string $view = 'filament.client.pages.auth.business-information';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    protected ?array $debtorData = null;

    public function mount(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('filament.client.auth.login'));
            return;
        }

        $user = Auth::user();

        $business = $user->businesses()->first();
        if ($business && $business->email_verified_at) {
            $this->redirect(route('filament.client.pages.dashboard'));
            return;
        }

        // Check if user is coming from a debtor registration link
        $this->debtorData = Session::get('debtor_registration');

        if ($business) {
            $this->form->fill([
                'business_name' => $business->name,
                'business_email' => $business->email,
                'business_phone' => $business->phone,
                'business_address' => $business->address,
                'registration_number' => $business->registration_number,
            ]);
        } elseif ($this->debtorData) {
            // Auto-populate from debtor information
            $this->form->fill([
                'business_name' => $this->debtorData['name'],
                'business_email' => $this->debtorData['email'],
                'registration_number' => $this->debtorData['kra_pin'],
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('business_name')
                    ->label('Business Name')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn () => $this->debtorData && isset($this->debtorData['name']))
                    ->helperText(fn () => $this->debtorData ? 'This business name is linked to your debtor record and cannot be changed.' : null)
                    ->columnSpanFull(),

                TextInput::make('business_email')
                    ->label('Business Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn () => $this->debtorData && isset($this->debtorData['email']))
                    ->helperText(fn () => $this->debtorData ? 'This email is linked to your debtor record and cannot be changed.' : null)
                    ->columnSpanFull(),

                TextInput::make('business_phone')
                    ->label('Business Phone')
                    ->tel()
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('business_address')
                    ->label('Business Address')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                TextInput::make('registration_number')
                    ->label('Business Registration Number (KRA PIN)')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn () => $this->debtorData && isset($this->debtorData['kra_pin']))
                    ->helperText(fn () => $this->debtorData ? 'This KRA PIN is linked to your debtor record and cannot be changed.' : null)
                    ->columnSpanFull(),

                Checkbox::make('terms_accepted')
                    ->label('I accept the Terms & Conditions and Privacy Policy')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function createOrUpdateBusiness(): void
    {
        $data = $this->form->getState();

        // If coming from debtor registration, ensure the locked fields match
        if ($this->debtorData) {
            $data['business_name'] = $this->debtorData['name'];
            $data['business_email'] = $this->debtorData['email'];
            $data['registration_number'] = $this->debtorData['kra_pin'];
        }

        $user = Auth::user();

        $business = $user->businesses()->first();

        if ($business) {
            $business->update([
                'name' => $data['business_name'],
                'email' => $data['business_email'],
                'phone' => $data['business_phone'],
                'address' => $data['business_address'],
                'registration_number' => $data['registration_number'],
            ]);
        } else {
            $business = Business::create([
                'name' => $data['business_name'],
                'email' => $data['business_email'],
                'phone' => $data['business_phone'],
                'address' => $data['business_address'],
                'registration_number' => $data['registration_number'],
            ]);

            BusinessUser::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'role' => 'owner',
            ]);
        }

        app(VerificationService::class)->sendBusinessEmailVerification($business);

        // If this is a debtor registration, clear the session data now that we've used it
        if ($this->debtorData) {
            Session::forget('debtor_registration');
        }

        Notification::make()
            ->title('Verification Email Sent')
            ->body('Please check your business email to verify your account before proceeding.')
            ->success()
            ->send();

        $this->redirect(route('filament.client.auth.email-verification'));
    }

    public function register(): ?RegistrationResponse
    {
        $this->createOrUpdateBusiness();

        return null;
    }

    public function hasLogo(): bool
    {
        return false;
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
