<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    use GuardsReviewWorkflow;

    protected static string $resource = PostResource::class;
}
