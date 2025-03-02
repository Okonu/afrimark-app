<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Filament\Client\Resources\DebtorResource;
use App\Models\Debtor;
use App\Services\Debtor\DebtorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class UpdatePayment extends Page
{
    protected static string $resource = DebtorResource::class;

    protected static string $view = 'filament.client.resources.debtor-resource.pages.update-payment';

    public ?Debtor $record = null;

    public ?array $data = [];

    public function mount($record): void
    {
        if (is_string($record)) {
            $record = Debtor::find($record);
        }

        if (!$record || !$record instanceof Debtor) {
            Notification::make()
                ->title('Error')
                ->body('Debtor record not found')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        $this->record = $record;

        $businessId = Auth::user()->businesses()->first()?->id;

        if ($record->business_id !== $businessId) {
            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        $this->form->fill([
            'current_amount' => $record->amount_owed,
            'payment_amount' => 0,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\TextInput::make('current_amount')
                            ->label('Current Amount Owed')
                            ->disabled()
                            ->prefix('KES'),

                        Forms\Components\TextInput::make('payment_amount')
                            ->label('Payment Amount')
                            ->numeric()
                            ->required()
                            ->prefix('KES')
                            ->rules([
                                function (string $attribute, $value, \Closure $fail) {
                                    if ($value <= 0) {
                                        $fail('Payment amount must be greater than zero.');
                                    }

                                    if ($value > $this->record->amount_owed) {
                                        $fail('Payment amount cannot exceed the current amount owed.');
                                    }
                                },
                            ]),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->helperText('e.g., Bank transfer reference, receipt number, etc.')
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('payment_document')
                            ->label('Payment Document')
                            ->helperText('Upload receipt or proof of payment (optional)')
                            ->directory('payment-documents')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (!$this->record) {
            Notification::make()
                ->title('Error')
                ->body('Debtor record not found')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        app(DebtorService::class)->updatePayment($this->record, $data['payment_amount']);

        if (isset($data['payment_document'])) {
            $this->record->documents()->create([
                'file_path' => $data['payment_document'],
                'original_filename' => $data['payment_document'],
                'uploaded_by' => Auth::id(),
                'type' => 'payment',
            ]);
        }

        Notification::make()
            ->title('Payment Updated')
            ->body('The payment has been recorded successfully.')
            ->success()
            ->send();

        $this->redirect($this->getResource()::getUrl('index'));
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('submit')
                ->label('Record Payment')
                ->submit('submit')
                ->color('success'),

            Forms\Components\Actions\Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResource()::getUrl('index'))
                ->color('secondary'),
        ];
    }
}
