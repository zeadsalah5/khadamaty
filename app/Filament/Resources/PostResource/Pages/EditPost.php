<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\Concerns\PublishesWithReview;
use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use GuardsReviewWorkflow, PublishesWithReview;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->publishAction(), Actions\DeleteAction::make()];
    }
}
