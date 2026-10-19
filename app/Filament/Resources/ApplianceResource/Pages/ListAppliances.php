<?php

namespace App\Filament\Resources\ApplianceResource\Pages;

use App\Filament\Resources\ApplianceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppliances extends ListRecords
{
    protected static string $resource = ApplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
