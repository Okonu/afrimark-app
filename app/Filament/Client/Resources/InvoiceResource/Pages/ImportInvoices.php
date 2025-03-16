<?php

namespace App\Filament\Client\Resources\InvoiceResource\Pages;

use App\Filament\Client\Resources\InvoiceResource;
use App\Exports\InvoiceTemplateExport;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InvoicesImport;

class ImportInvoices extends Page
{
    use WithFileUploads;

    protected static string $resource = InvoiceResource::class;

    protected static string $view = 'filament.client.resources.invoice-resource.import-invoices';

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
                Forms\Components\Section::make('Import Invoices')
                    ->schema([
                        Forms\Components\FileUpload::make('file')
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
            $import = new InvoicesImport($business->id, Auth::id());
            $import->hasHeaders = $data['has_headers'] ?? true;

            Excel::import($import, storage_path('app/public/' . $data['file']));

            $importCount = $import->getRowCount();

            Notification::make()
                ->title('Import Successful')
                ->body("Successfully imported {$importCount} invoices.")
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
        return Excel::download(new InvoiceTemplateExport(), 'invoices-import-template.xlsx');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')
                ->label('Import Invoices')
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
