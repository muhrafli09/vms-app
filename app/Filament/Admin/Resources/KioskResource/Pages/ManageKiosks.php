<?php

namespace App\Filament\Admin\Resources\KioskResource\Pages;

use App\Filament\Admin\Resources\KioskResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKiosks extends ManageRecords
{
    protected static string $resource = KioskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
