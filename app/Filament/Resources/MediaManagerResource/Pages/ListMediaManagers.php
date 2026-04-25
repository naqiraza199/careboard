<?php

namespace App\Filament\Resources\MediaManagerResource\Pages;

use App\Filament\Resources\MediaManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMediaManagers extends ListRecords
{
    protected static string $resource = MediaManagerResource::class;

    protected static string $view = 'filament.resources.pages.list-media-managers';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
