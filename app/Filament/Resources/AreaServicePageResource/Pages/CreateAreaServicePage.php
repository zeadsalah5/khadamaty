<?php

namespace App\Filament\Resources\AreaServicePageResource\Pages;

use App\Filament\Resources\AreaServicePageResource;
use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use Filament\Resources\Pages\CreateRecord;

class CreateAreaServicePage extends CreateRecord
{
    use GuardsReviewWorkflow;

    protected static string $resource = AreaServicePageResource::class;
}
