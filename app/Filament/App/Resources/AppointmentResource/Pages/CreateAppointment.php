<?php

namespace App\Filament\App\Resources\AppointmentResource\Pages;

use App\Filament\App\Resources\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VisitorAppointmentMail;
use App\Services\QrCodeService;

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
    
    protected function afterCreate(): void
    {
        $visit = $this->record;
        
        // Generate QR Code
        $qrCodeService = new QrCodeService();
        $qrCodeService->generate($visit->uuid);
        
        // Send email to visitor if email exists
        if ($visit->visitor->email) {
            try {
                Mail::to($visit->visitor->email)->send(new VisitorAppointmentMail($visit));
                
                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('Appointment Created')
                    ->body('Email with QR code has been sent to ' . $visit->visitor->email)
                    ->send();
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title('Appointment Created')
                    ->body('Appointment created but failed to send email: ' . $e->getMessage())
                    ->send();
            }
        } else {
            \Filament\Notifications\Notification::make()
                ->success()
                ->title('Appointment Created')
                ->body('Appointment created. No email sent (visitor has no email address).')
                ->send();
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
