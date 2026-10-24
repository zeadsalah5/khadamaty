<?php

namespace App\Filament\Resources\BrandServicePageResource\Pages;

use App\Filament\Resources\Concerns\GuardsCompleteness;
use App\Filament\Resources\BrandServicePageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrandServicePage extends CreateRecord
{
    use GuardsCompleteness;

    protected static string $resource = BrandServicePageResource::class;
}
