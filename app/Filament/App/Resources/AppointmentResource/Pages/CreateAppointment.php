<?php

namespace App\Filament\App\Resources\AppointmentResource\Pages;

use App\Filament\App\Resources\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create or update visitor
        $visitor = \App\Models\Visitor::updateOrCreate(
            ['phone' => $data['visitor_phone']],
            [
                'name' => $data['visitor_name'],
                'email' => $data['visitor_email'] ?? null,
                'company' => $data['visitor_company'] ?? null,
            ]
        );
        
        // Remove temporary fields and set visitor_id
        unset($data['visitor_name'], $data['visitor_phone'], $data['visitor_email'], $data['visitor_company']);
        
        $data['visitor_id'] = $visitor->id;
        $data['uuid'] = Str::uuid();
        $data['status'] = 'scheduled';
        $data['created_by'] = Auth::id();
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
