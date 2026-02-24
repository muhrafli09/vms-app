<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk - {{ $kiosk->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
            <h1 class="text-2xl font-bold text-center mb-6">{{ $kiosk->name }}</h1>
            <p class="text-gray-600 text-center mb-8">Visitor Check-in System</p>
            
            <div class="space-y-4">
                <a href="{{ route('kiosk.scan.form', $kiosk->token) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg text-center">
                    Scan QR Code
                </a>
                
                <a href="{{ route('kiosk.checkin.form', $kiosk->token) }}" class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-6 rounded-lg text-center">
                    Walk-in Check-in
                </a>
            </div>
        </div>
    </div>
</body>
</html>
