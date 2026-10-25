<?php

namespace App\Filament\Resources\AreaServicePageResource\Pages;

use App\Filament\Resources\AreaServicePageResource;
use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\Concerns\PublishesWithReview;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAreaServicePage extends EditRecord
{
    use GuardsReviewWorkflow, PublishesWithReview;

    protected static string $resource = AreaServicePageResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->publishAction(), Actions\DeleteAction::make()];
    }
}
