<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\ManageRecords;

class ManageBookings extends ManageRecords
{
    protected static string $resource = BookingResource::class;

    // مفيش إنشاء يدوي — الحجوزات بتيجي من فورم الموقع.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
