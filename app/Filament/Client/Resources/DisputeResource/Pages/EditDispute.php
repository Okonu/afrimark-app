<?php

namespace App\Filament\Client\Resources\DisputeResource\Pages;

use App\Filament\Client\Resources\DisputeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDispute extends EditRecord
{
    protected static string $resource = DisputeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
