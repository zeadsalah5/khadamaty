<?php

namespace App\Filament\Resources\BrandServicePageResource\Pages;

use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\BrandServicePageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrandServicePage extends CreateRecord
{
    use GuardsReviewWorkflow;

    protected static string $resource = BrandServicePageResource::class;
}
