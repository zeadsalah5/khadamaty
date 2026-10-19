<?php

namespace App\Filament\Resources\ApplianceResource\Pages;

use App\Filament\Resources\Concerns\GuardsCompleteness;
use App\Filament\Resources\ApplianceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppliance extends EditRecord
{
    use GuardsCompleteness;

    protected static string $resource = ApplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
