<?php

namespace App\Filament\Resources\BrandServicePageResource\Pages;

use App\Filament\Resources\BrandServicePageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBrandServicePages extends ListRecords
{
    protected static string $resource = BrandServicePageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
