<?php

namespace App\Filament\App\Resources\AppointmentResource\Pages;

use App\Filament\App\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load visitor data into form fields
        if (isset($data['visitor_id']) && $this->record->visitor) {
            $data['visitor_name'] = $this->record->visitor->name;
            $data['visitor_phone'] = $this->record->visitor->phone;
            $data['visitor_email'] = $this->record->visitor->email;
            $data['visitor_company'] = $this->record->visitor->company;
        }
        
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update visitor
        if ($this->record->visitor_id) {
            $this->record->visitor->update([
                'name' => $data['visitor_name'],
                'phone' => $data['visitor_phone'],
                'email' => $data['visitor_email'] ?? null,
                'company' => $data['visitor_company'] ?? null,
            ]);
        }
        
        // Remove temporary fields
        unset($data['visitor_name'], $data['visitor_phone'], $data['visitor_email'], $data['visitor_company']);
        
        return $data;
    }
}
