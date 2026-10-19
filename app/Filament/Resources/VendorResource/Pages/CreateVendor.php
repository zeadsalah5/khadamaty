<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\Concerns\GuardsCompleteness;
use App\Filament\Resources\VendorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendor extends CreateRecord
{
    use GuardsCompleteness;

    protected static string $resource = VendorResource::class;
}
