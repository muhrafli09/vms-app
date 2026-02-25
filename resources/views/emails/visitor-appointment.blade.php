<x-mail::message>
# Appointment Confirmation

Hello **{{ $visit->visitor->name }}**,

Your appointment has been scheduled successfully!

## Appointment Details

- **Date & Time:** {{ $visit->scheduled_time->format('l, F j, Y \a\t g:i A') }}
- **Meeting with:** {{ $visit->employee->full_name }}
- **Department:** {{ $visit->employee->department ? $visit->employee->department->name : 'N/A' }}
- **Purpose:** {{ $visit->purpose ?? 'N/A' }}

## Check-in Instructions

Please scan the QR code below at the kiosk when you arrive:

<x-mail::panel>
<div style="text-align: center;">
<img src="{{ $message->embed(app(\App\Services\QrCodeService::class)->getQrCodePath($visit)) }}" alt="QR Code" style="width: 200px; height: 200px;">
</div>
</x-mail::panel>

**Or use this code:** `{{ $visit->uuid }}`

<x-mail::button :url="config('app.url')">
Visit Our Website
</x-mail::button>

If you need to reschedule or cancel, please contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
