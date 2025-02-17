<?php

namespace App\Filament\Client\Resources\BusinessResource\Pages;

use App\Filament\Client\Resources\BusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBusiness extends ViewRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
