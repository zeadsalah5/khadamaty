<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\Concerns\GuardsCompleteness;
use App\Filament\Resources\ProductCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCategory extends CreateRecord
{
    use GuardsCompleteness;

    protected static string $resource = ProductCategoryResource::class;
}
