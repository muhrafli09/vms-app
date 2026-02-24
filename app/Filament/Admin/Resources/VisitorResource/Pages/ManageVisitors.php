<?php

namespace App\Filament\Admin\Resources\VisitorResource\Pages;

use App\Filament\Admin\Resources\VisitorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVisitors extends ManageRecords
{
    protected static string $resource = VisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
