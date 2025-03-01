<?php

namespace App\Filament\Client\Resources\DisputeResource\Pages;

use App\Filament\Client\Resources\DisputeResource;
use App\Models\Dispute;
use App\Services\Dispute\DisputeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RespondToDispute extends Page
{
    protected static string $resource = DisputeResource::class;

    protected static string $view = 'filament.client.resources.dispute-resource.pages.respond-to-dispute';

    public ?Dispute $record = null;

    public ?array $data = [];

    public function mount(Dispute $record): void
    {
        $this->record = $record;

        $businessId = Auth::user()->businesses()->first()?->id;

        if ($record->business_id !== $businessId || $record->status !== 'pending') {
            $this->redirect($this->getResource()::getUrl('index'));
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dispute Response')
                    ->schema([
                        Forms\Components\Select::make('response_action')
                            ->label('Choose Action')
                            ->options([
                                'approve' => 'Approve Dispute (Remove Listing)',
                                'reject' => 'Reject Dispute (Maintain Listing)',
                                'request_info' => 'Request More Information',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('response_notes')
                            ->label('Response Notes')
                            ->required()
                            ->maxLength(1000),

                        Forms\Components\FileUpload::make('response_documents')
                            ->label('Supporting Documents')
                            ->helperText('Upload any documents to support your response')
                            ->multiple()
                            ->directory('dispute-responses')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(DisputeService $disputeService): void
    {
        $data = $this->form->getState();

        switch ($data['response_action']) {
            case 'approve':
                $disputeService->updateDisputeStatus($this->record, 'resolved_approved', $data['response_notes']);
                $message = 'Dispute approved. The listing has been removed.';
                break;

            case 'reject':
                $disputeService->updateDisputeStatus($this->record, 'resolved_rejected', $data['response_notes']);
                $message = 'Dispute rejected. The listing will remain active.';
                break;

            case 'request_info':
                $disputeService->updateDisputeStatus($this->record, 'under_review', $data['response_notes']);
                $message = 'Additional information has been requested.';
                break;
        }

        if (isset($data['response_documents']) && is_array($data['response_documents'])) {
            foreach ($data['response_documents'] as $document) {
                $this->record->documents()->create([
                    'file_path' => $document,
                    'original_filename' => $document,
                    'uploaded_by' => Auth::id(),
                    'type' => 'response',
                ]);
            }
        }

        Notification::make()
            ->title('Response Submitted')
            ->body($message)
            ->success()
            ->send();

        $this->redirect($this->getResource()::getUrl('index'));
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('submit')
                ->label('Submit Response')
                ->submit('submit')
                ->color('primary'),

            Forms\Components\Actions\Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResource()::getUrl('index'))
                ->color('secondary'),
        ];
    }
}
