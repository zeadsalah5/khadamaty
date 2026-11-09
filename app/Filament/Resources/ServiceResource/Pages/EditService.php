<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\Concerns\PublishesWithReview;
use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    use GuardsReviewWorkflow, PublishesWithReview;

    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->publishAction(), Actions\DeleteAction::make()];
    }
}
