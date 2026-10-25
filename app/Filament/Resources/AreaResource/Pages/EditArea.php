<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\Concerns\PublishesWithReview;
use App\Filament\Resources\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArea extends EditRecord
{
    use GuardsReviewWorkflow, PublishesWithReview;

    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->publishAction(), Actions\DeleteAction::make()];
    }
}
