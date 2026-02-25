<x-mail::message>
# Visitor Check-in Notification

Hello **{{ $visit->employee->full_name }}**,

Your visitor has checked in!

## Visitor Information

<x-mail::table>
| | |
|:---|:---|
| **Name** | {{ $visit->visitor->name }} |
| **Phone** | {{ $visit->visitor->phone }} |
| **Email** | {{ $visit->visitor->email ?? 'N/A' }} |
| **Company** | {{ $visit->visitor->company ?? 'N/A' }} |
| **Check-in Time** | {{ $visit->arrival->format('l, F j, Y \a\t g:i A') }} |
| **Purpose** | {{ $visit->purpose ?? 'N/A' }} |
</x-mail::table>

@if($visit->photo)
## Visitor Photo

<x-mail::panel>
<div style="text-align: center;">
<img src="{{ $message->embed(storage_path('app/public/' . $visit->photo)) }}" alt="Visitor Photo" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover;">
</div>
</x-mail::panel>

The visitor photo is also attached to this email.
@endif

@if($visit->status === 'scheduled')
**Note:** This was a scheduled appointment.
@else
**Note:** This was a walk-in visit.
@endif

<x-mail::button :url="config('app.url') . '/app/manage/visits'">
View All Visits
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
