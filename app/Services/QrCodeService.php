<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generate(string $uuid): string
    {
        // Generate QR code using API and save to storage
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($uuid);
        
        // Download QR code image
        $qrCodeImage = file_get_contents($qrCodeUrl);
        
        // Save to public storage
        $path = 'qrcodes/' . $uuid . '.png';
        Storage::disk('public')->put($path, $qrCodeImage);
        
        return $path;
    }
    
    public function generateForVisit(Visit $visit): string
    {
        return $this->generate($visit->uuid);
    }

    public function getQrCodePath(Visit $visit): string
    {
        $path = 'qrcodes/' . $visit->uuid . '.png';
        
        // Generate if not exists
        if (!Storage::disk('public')->exists($path)) {
            $this->generate($visit->uuid);
        }
        
        return storage_path('app/public/' . $path);
    }

    public function deleteQrCode(Visit $visit): bool
    {
        $path = 'qrcodes/' . $visit->uuid . '.png';
        
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return true;
    }
}
