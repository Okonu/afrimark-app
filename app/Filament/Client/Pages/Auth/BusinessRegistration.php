<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Business;
use App\Models\BusinessDocument;
use App\Models\BusinessUser;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class BusinessRegistration extends Register
{
    use WithRateLimiting;

    protected static string $view = 'filament.client.pages.auth.business-registration';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirect('/client');
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Business Information')
                        ->icon('heroicon-o-building-office')
                        ->schema([
                            TextInput::make('business_name')
                                ->label('Business Name')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            TextInput::make('business_registration_number')
                                ->label('Registration Number')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            TextInput::make('business_email')
                                ->label('Business Email')
                                ->email()
                                ->required()
                                ->maxLength(255)
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
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ]),
                    Wizard\Step::make('Account Details')
                        ->icon('heroicon-o-user')
                        ->schema([
                            TextInput::make('name')
                                ->label('Your Name')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            TextInput::make('email')
                                ->label('Your Email')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->unique('users')
                                ->columnSpanFull(),
                        ]),
                    Wizard\Step::make('Documents')
                        ->icon('heroicon-o-document')
                        ->schema([
                            FileUpload::make('registration_document')
                                ->label('Registration Document')
                                ->helperText('Upload your business registration certificate (PDF only, max 10MB)')
                                ->required()
                                ->disk('public')
                                ->directory('business-documents/registration')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->columnSpanFull(),
                            FileUpload::make('tax_document')
                                ->label('Tax Document')
                                ->helperText('Upload your tax registration document (PDF only, max 10MB)')
                                ->required()
                                ->disk('public')
                                ->directory('business-documents/tax')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->columnSpanFull(),
                        ]),
                ])
                    ->persistStepInQueryString()
                    ->columnSpanFull()
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="lg"
                            class="w-full"
                        >
                            Complete Registration
                        </x-filament::button>
                    BLADE)))
            ])
            ->statePath('data');
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                ]))
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        DB::transaction(function () use ($data) {

            $business = Business::create([
                'name' => $data['business_name'],
                'email' => $data['business_email'],
                'phone' => $data['business_phone'],
                'address' => $data['business_address'],
                'registration_number' => $data['business_registration_number'],
            ]);

            $password = Str::random(12);
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($password),
            ]);

            BusinessUser::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'role' => 'owner',
            ]);

            foreach (['registration_document', 'tax_document'] as $documentKey) {
                if (isset($data[$documentKey])) {
                    BusinessDocument::create([
                        'business_id' => $business->id,
                        'type' => str_replace('_document', '', $documentKey),
                        'file_path' => $data[$documentKey],
                        'original_filename' => $data[$documentKey],
                        'status' => 'pending',
                    ]);
                }
            }

            auth()->login($user);
        });

        return app(RegistrationResponse::class);
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.client.home');
    }
}
