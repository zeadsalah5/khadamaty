<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    use GuardsReviewWorkflow;

    protected static string $resource = BrandResource::class;
}
