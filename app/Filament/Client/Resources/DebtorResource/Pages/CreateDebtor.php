<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Filament\Client\Resources\DebtorResource;
use App\Services\Debtor\DebtorService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateDebtor extends CreateRecord
{
    protected static string $resource = DebtorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'pending';
        $data['listing_goes_live_at'] = now()->addDays(7);

        return $data;
    }

    protected function afterCreate(): void
    {
        $debtorService = app(DebtorService::class);
        $business = Auth::user()->businesses()->first();

        if ($business) {
            $this->record->businesses()->attach($business->id, [
                'amount_owed' => $this->data['amount_owed'] ?? 0,
            ]);
        }

        $documents = $this->data['documents'] ?? [];
        foreach ($documents as $document) {
            $this->record->documents()->create([
                'file_path' => $document,
                'original_filename' => $document,
                'uploaded_by' => Auth::id(),
            ]);
        }

        $debtorService->sendDebtorNotification($this->record);

        Notification::make()
            ->title('Debtor Created')
            ->body('The debtor has been added and a notification has been sent.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
