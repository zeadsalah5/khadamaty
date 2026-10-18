<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Concerns\GuardsPublishState;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    use GuardsPublishState;

    protected static string $resource = PostResource::class;
}
