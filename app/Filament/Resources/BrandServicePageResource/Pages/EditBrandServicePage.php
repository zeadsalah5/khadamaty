<?php

namespace App\Filament\Resources\BrandServicePageResource\Pages;

use App\Filament\Resources\Concerns\GuardsCompleteness;
use App\Filament\Resources\BrandServicePageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrandServicePage extends EditRecord
{
    use GuardsCompleteness;

    protected static string $resource = BrandServicePageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
