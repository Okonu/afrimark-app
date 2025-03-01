<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Services\Business\VerificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Pages\SimplePage;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class BusinessInformation extends SimplePage
{
    protected static string $view = 'filament.client.pages.auth.business-information';

    public ?array $data = [];

    public static function canView(): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return 'Business Information';
    }

    public static function getSlug(): string
    {
        return 'business-information';
    }

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

        if ($business) {
            $this->form->fill([
                'business_name' => $business->name,
                'business_email' => $business->email,
                'business_phone' => $business->phone,
                'business_address' => $business->address,
                'registration_number' => $business->registration_number,
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
                    ->maxLength(500)
                    ->columnSpanFull(),

                TextInput::make('registration_number')
                    ->label('Business Registration Number')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Checkbox::make('terms_accepted')
                    ->label('I accept the Terms & Conditions and Privacy Policy')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function submit(VerificationService $verificationService): void
    {
        $data = $this->form->getState();

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

        $verificationService->sendBusinessEmailVerification($business);

        Notification::make()
            ->title('Verification Email Sent')
            ->body('Please check your business email to verify your account before proceeding.')
            ->success()
            ->send();

        $this->redirect(route('filament.client.auth.email-verification'));
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSubmitFormAction(),
        ];
    }

    protected function getSubmitFormAction(): Action
    {
        return Action::make('submit')
            ->label('Save & Continue')
            ->submit('submit');
    }
}
