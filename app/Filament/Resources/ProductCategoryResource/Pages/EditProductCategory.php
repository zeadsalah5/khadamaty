<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\Concerns\GuardsCompleteness;
use App\Filament\Resources\ProductCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductCategory extends EditRecord
{
    use GuardsCompleteness;

    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
