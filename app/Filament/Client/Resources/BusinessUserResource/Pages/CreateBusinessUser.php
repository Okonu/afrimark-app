<?php

namespace App\Filament\Client\Resources\BusinessUserResource\Pages;

use App\Filament\Client\Resources\BusinessUserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessUser extends CreateRecord
{
    protected static string $resource = BusinessUserResource::class;
}
