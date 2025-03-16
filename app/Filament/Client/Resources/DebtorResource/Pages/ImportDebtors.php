<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Filament\Client\Resources\DebtorResource;
use App\Exports\DebtorTemplateExport;
use App\Imports\DebtorsImport;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ImportDebtors extends Page
{
    protected static string $resource = DebtorResource::class;
    protected static string $view = 'filament.client.resources.debtor-resource.import-debtors';

    public array $data = [];

    public function mount(): void
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            $this->redirect(route('filament.client.auth.business-information'));
        }

        $this->form->fill();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Import Debtors')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Upload Excel/CSV File')
                            ->helperText('Download the template below to ensure your file is in the correct format')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv'
                            ])
                            ->maxSize(5120)
                            ->required(),

                        Forms\Components\Checkbox::make('has_headers')
                            ->label('File has headers')
                            ->default(true),

                        Forms\Components\Checkbox::make('liability_confirmation')
                            ->label('I confirm that all the information provided is accurate and I bear full liability for its correctness')
                            ->required(),

                        Forms\Components\Checkbox::make('terms_accepted')
                            ->label('I have read and accepted the Terms & Conditions')
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (empty($data['file']) ||
            !isset($data['liability_confirmation']) || $data['liability_confirmation'] !== true ||
            !isset($data['terms_accepted']) || $data['terms_accepted'] !== true) {

            Notification::make()
                ->title('Validation Error')
                ->body('Please fill in all required fields and accept the terms.')
                ->danger()
                ->send();

            return;
        }

        $business = Auth::user()->businesses()->first();

        try {
            $filePath = $data['file'];

            if (is_array($filePath)) {
                $filePath = $filePath[0];
            }

            $import = new DebtorsImport($business->id, Auth::id());
            $import->hasHeaders = $data['has_headers'] ?? true;

            Excel::import($import, storage_path('app/public/' . $filePath));

            $importCount = $import->getRowCount();

            Notification::make()
                ->title('Import Successful')
                ->body("Successfully imported {$importCount} debtors. Please add supporting documents for each debtor.")
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('There was an error importing the file: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new DebtorTemplateExport(), 'debtors-import-template.xlsx');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')
                ->label('Import Debtors')
                ->action('submit')
                ->color('primary'),

            Action::make('download_template')
                ->label('Download Template')
                ->action('downloadTemplate')
                ->color('primary'),

            Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResource()::getUrl('index'))
                ->color('primary'),
        ];
    }
}
