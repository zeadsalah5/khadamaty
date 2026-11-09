<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ManageRecords;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    // مفيش إنشاء يدوي — الطلبات بتيجي من الـ checkout.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
