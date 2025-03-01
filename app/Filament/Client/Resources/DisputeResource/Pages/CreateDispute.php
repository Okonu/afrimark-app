<?php

namespace App\Filament\Client\Resources\DisputeResource\Pages;

use App\Filament\Client\Resources\DisputeResource;
use App\Services\Dispute\DisputeService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Models\Debtor;

class CreateDispute extends CreateRecord
{
    protected static string $resource = DisputeResource::class;

    public function mount(): void
    {
        parent::mount();

        $debtorId = request()->query('debtor');

        if ($debtorId) {
            $debtor = Debtor::find($debtorId);

            if ($debtor && $debtor->kra_pin === Auth::user()->businesses()->first()?->registration_number) {
                $this->form->fill([
                    'debtor_id' => $debtorId,
                ]);
            } else {
                $this->redirect($this->getResource()::getUrl('index'));
            }
        } else {
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function afterCreate(): void
    {
        $documents = $this->data['documents'] ?? [];

        foreach ($documents as $document) {
            $this->record->documents()->create([
                'file_path' => $document,
                'original_filename' => $document,
                'uploaded_by' => Auth::id(),
            ]);
        }

        $debtor = $this->record->debtor;
        $debtor->status = 'disputed';
        $debtor->save();

        $disputeService = app(DisputeService::class);
        $disputeService->notifyLister($this->record);

        Notification::make()
            ->title('Dispute Created')
            ->body('Your dispute has been submitted successfully.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
