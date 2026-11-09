<?php

namespace App\Filament\Resources\BrandServicePageResource\Pages;

use App\Filament\Resources\Concerns\GuardsReviewWorkflow;
use App\Filament\Resources\Concerns\PublishesWithReview;
use App\Filament\Resources\BrandServicePageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrandServicePage extends EditRecord
{
    use GuardsReviewWorkflow, PublishesWithReview;

    protected static string $resource = BrandServicePageResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->publishAction(), Actions\DeleteAction::make()];
    }
}
