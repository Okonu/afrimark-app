<?php

namespace App\Filament\Client\Resources\BusinessUserResource\Pages;

use App\Filament\Client\Resources\BusinessUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessUser extends EditRecord
{
    protected static string $resource = BusinessUserResource::class;

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
