<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Business;
use App\Models\BusinessDocument;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\SimplePage;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DocumentUpload extends SimplePage
{
    protected static string $view = 'filament.client.pages.auth.document-upload';

    public ?array $data = [];

    public $business;

    public function mount(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('filament.client.auth.login'));
            return;
        }

        $user = Auth::user();
        $this->business = $user->businesses()->first();


        if (!$this->business) {
            Notification::make()
                ->title('Business Information Required')
                ->body('Please complete your business profile first.')
                ->warning()
                ->send();

            $this->redirect(route('filament.client.auth.business-information'));
            return;
        }

        $this->form->fill([
            'certificate_of_incorporation' => $this->business->documents()
                ->where('type', 'certificate_of_incorporation')
                ->first()?->file_path,
            'tax_pin' => $this->business->documents()
                ->where('type', 'tax_pin')
                ->first()?->file_path,
            'cr12_cr13' => $this->business->documents()
                ->where('type', 'cr12_cr13')
                ->first()?->file_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('certificate_of_incorporation')
                    ->label('Certificate of Incorporation')
                    ->helperText('Upload your business registration certificate (PDF only, max 10MB)')
                    ->disk('public')
                    ->directory('business-documents/incorporation')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->columnSpanFull(),

                FileUpload::make('tax_pin')
                    ->label('KRA PIN Document')
                    ->helperText('Upload your tax registration document (PDF only, max 10MB)')
                    ->disk('public')
                    ->directory('business-documents/tax')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->columnSpanFull(),

                FileUpload::make('cr12_cr13')
                    ->label('CR12 or CR13 Document')
                    ->helperText('Upload your company CR12 or CR13 document (PDF only, max 10MB)')
                    ->disk('public')
                    ->directory('business-documents/company')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $user = Auth::user();
        $this->business = $user->businesses()->first();

        if (!$this->business) {
            Notification::make()
                ->title('Error')
                ->body('Business information not found. Please complete your business profile first.')
                ->danger()
                ->send();

            $this->redirect(route('filament.client.auth.business-information'));
            return;
        }

        $data = $this->form->getState();

        if (isset($data['certificate_of_incorporation'])) {
            $this->saveDocument('certificate_of_incorporation', $data['certificate_of_incorporation']);
        }

        if (isset($data['tax_pin'])) {
            $this->saveDocument('tax_pin', $data['tax_pin']);
        }

        if (isset($data['cr12_cr13'])) {
            $this->saveDocument('cr12_cr13', $data['cr12_cr13']);
        }

        Notification::make()
            ->title('Documents Uploaded')
            ->body('Your business documents have been successfully uploaded.')
            ->success()
            ->send();

        $this->redirect(route('filament.client.pages.dashboard'));
    }

    protected function saveDocument($type, $path)
    {
        if (!$this->business) {
            Log::error('Business is null in saveDocument', [
                'type' => $type,
                'path' => $path
            ]);
            return;
        }

        try {
            $existingDoc = $this->business->documents()
                ->where('type', $type)
                ->first();

            if ($existingDoc) {
                $existingDoc->update([
                    'file_path' => $path,
                    'original_filename' => $path,
                    'status' => 'pending',
                ]);

            } else {
                $doc = BusinessDocument::create([
                    'business_id' => $this->business->id,
                    'type' => $type,
                    'file_path' => $path,
                    'original_filename' => $path,
                    'status' => 'pending',
                ]);

            }
        } catch (\Exception $e) {
            Log::error('Error saving document', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error Saving Document')
                ->body('An error occurred while saving the document: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function skipDocumentUpload()
    {
        $this->redirect(route('filament.client.pages.dashboard'));
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Upload Documents')
                ->submit('submit'),

            Action::make('skip')
                ->label('Skip for Now')
                ->color('secondary')
                ->action('skipDocumentUpload'),
        ];
    }
}
