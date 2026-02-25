<?php

namespace App\Filament\Admin\Resources\VisitResource\Pages;

use App\Filament\Admin\Resources\VisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Mail;
use App\Mail\HostVisitorCheckinMail;

class ManageVisits extends ManageRecords
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Validate photo is captured
                    if (empty($data['photo'])) {
                        throw new \Exception('Photo is required. Please capture visitor photo.');
                    }
                    
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
                    
                    // Save photo from base64
                    if (!empty($data['photo']) && str_starts_with($data['photo'], 'data:image')) {
                        $image = $data['photo'];
                        $image = str_replace('data:image/jpeg;base64,', '', $image);
                        $image = str_replace(' ', '+', $image);
                        $imageName = 'visits/' . uniqid() . '.jpg';
                        \Storage::disk('public')->put($imageName, base64_decode($image));
                        $data['photo'] = $imageName;
                    } else {
                        unset($data['photo']);
                    }
                    
                    $data['visitor_id'] = $visitor->id;
                    $data['uuid'] = \Illuminate\Support\Str::uuid();
                    $data['status'] = 'checked_in';
                    $data['arrival'] = now();
                    
                    return $data;
                })
                ->after(function ($record) {
                    // Send email notification to host
                    $record->load('visitor', 'employee.user');
                    
                    if ($record->employee->user && $record->employee->user->email) {
                        try {
                            Mail::to($record->employee->user->email)->send(new HostVisitorCheckinMail($record));
                        } catch (\Exception $e) {
                            \Log::error('Failed to send host notification: ' . $e->getMessage());
                        }
                    }
                }),
        ];
    }
}
