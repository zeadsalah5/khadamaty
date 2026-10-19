<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\Concerns\GuardsCompleteness;
use App\Filament\Resources\AreaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArea extends CreateRecord
{
    use GuardsCompleteness;

    protected static string $resource = AreaResource::class;
}
