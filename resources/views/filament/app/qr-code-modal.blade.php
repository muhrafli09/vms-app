<div class="p-6 text-center">
    @php
        $qrService = app(\App\Services\QrCodeService::class);
        $qrImageUrl = $qrService->generateForVisit($visit);
        $checkInUrl = url('/kiosk/scan/' . $visit->uuid);
    @endphp

    <div class="mb-4">
        <img src="{{ $qrImageUrl }}" alt="QR Code" class="mx-auto rounded-lg shadow-lg" style="max-width: 300px;">
    </div>
    
    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
        <p><strong>Visitor:</strong> {{ $visit->visitor }}</p>
        @if($visit->visitor_company)
        <p><strong>Company:</strong> {{ $visit->visitor_company }}</p>
        @endif
        @if($visit->visitor_phone)
        <p><strong>Phone:</strong> {{ $visit->visitor_phone }}</p>
        @endif
        <p><strong>Scheduled:</strong> {{ \Carbon\Carbon::parse($visit->scheduled_time)->format('M d, Y H:i') }}</p>
        <p><strong>Host:</strong> {{ $visit->employee->full_name }}</p>
        @if($visit->purpose)
        <p><strong>Purpose:</strong> {{ $visit->purpose }}</p>
        @endif
    </div>

    <div class="mt-4 p-3 bg-gray-100 dark:text-gray-800 rounded-lg">
        <p class="text-xs text-gray-500 mb-1">UUID for Kiosk Scan:</p>
        <code class="text-xs break-all">{{ $visit->uuid }}</code>
    </div>

    <div class="mt-6">
        <button onclick="navigator.clipboard.writeText('{{ $visit->uuid }}')"
                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Copy UUID
        </button>
    </div>
</div>
