<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    use GuardsReviewWorkflow;

    protected static string $resource = ServiceResource::class;
}
