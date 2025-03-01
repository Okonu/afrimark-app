<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Filament\Client\Resources\DebtorResource;
use App\Services\Debtor\DebtorService;
use App\Exports\DebtorTemplateExport;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DebtorsImport;

class ImportDebtors extends Page
{
    use WithFileUploads;

    protected static string $resource = DebtorResource::class;

    protected static string $view = 'filament.client.resources.debtor-resource.pages.import-debtors';

    public $file;
    public ?array $data = [];

    public function mount(): void
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            $this->redirect(route('filament.client.auth.business-information'));
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Import Debtors')
                    ->schema([
                        Forms\Components\FileUpload::make('file')
                            ->label('Upload Excel/CSV File')
                            ->helperText('Download the template below to ensure your file is in the correct format')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
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

    public function submit(DebtorService $debtorService): void
    {
        $data = $this->form->getState();

        $business = Auth::user()->businesses()->first();

        try {
            $import = new DebtorsImport($business->id, Auth::id());
            $import->hasHeaders = $data['has_headers'] ?? true;

            Excel::import($import, $data['file']);

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
                ->color('secondary'),

            Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResource()::getUrl('index'))
                ->color('secondary'),
        ];
    }
}
