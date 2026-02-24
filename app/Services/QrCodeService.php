<?php

namespace App\Services;

use App\Models\Visit;

class QrCodeService
{
    public function generateForVisit(Visit $visit): string
    {
        $uuid = $visit->uuid;
        
        // Use QR Server API - reliable and free
        return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($uuid);
    }

    public function getQrCodePath(Visit $visit): string
    {
        return $this->generateForVisit($visit);
    }

    public function deleteQrCode(Visit $visit): bool
    {
        return true;
    }
}
