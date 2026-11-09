<?php

namespace App\Filament\Resources\ApplianceResource\Pages;

use App\Filament\Resources\Concerns\GuardsCompleteness;
use App\Filament\Resources\ApplianceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppliance extends CreateRecord
{
    use GuardsCompleteness;

    protected static string $resource = ApplianceResource::class;
}
