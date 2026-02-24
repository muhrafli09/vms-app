<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR - {{ $kiosk->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-center mb-6">Scan Appointment QR Code</h1>
            
            <div id="reader" class="mb-6 rounded-lg overflow-hidden"></div>
            
            <div id="result" class="hidden mb-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-green-800 font-semibold">QR Code Scanned!</p>
                    <p class="text-sm text-green-600">Please take a selfie to complete check-in</p>
                </div>
                
                <div class="mb-4">
                    <video id="video" autoplay class="w-full rounded-lg border" style="display:none;"></video>
                    <canvas id="canvas" class="w-full rounded-lg border" style="display:none;"></canvas>
                    <img id="photo" class="w-full rounded-lg border" style="display:none;">
                </div>
                
                <button id="captureBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg mb-2">
                    Take Photo
                </button>
                <button id="retakeBtn" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg mb-2" style="display:none;">
                    Retake Photo
                </button>
                <button id="submitBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg" style="display:none;">
                    Complete Check-in
                </button>
            </div>
            
            <div id="error" class="hidden mb-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800 font-semibold">Error</p>
                    <p class="text-sm text-red-600" id="errorMsg"></p>
                </div>
            </div>
            
            <a href="{{ route('kiosk.index', $kiosk->token) }}" class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg text-center">
                Back
            </a>
        </div>
    </div>

    <script>
        let scannedUuid = null;
        let photoData = null;
        let stream = null;
        
        const html5QrCode = new Html5Qrcode("reader");
        
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            onScanSuccess
        ).catch(err => {
            console.error("QR Scanner error:", err);
        });
        
        function onScanSuccess(decodedText) {
            scannedUuid = decodedText;
            html5QrCode.stop();
            document.getElementById('reader').style.display = 'none';
            document.getElementById('result').classList.remove('hidden');
            startCamera();
        }
        
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                const video = document.getElementById('video');
                video.srcObject = stream;
                video.style.display = 'block';
            } catch (err) {
                showError('Camera access denied');
            }
        }
        
        document.getElementById('captureBtn').addEventListener('click', () => {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const photo = document.getElementById('photo');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            photoData = canvas.toDataURL('image/jpeg');
            photo.src = photoData;
            
            video.style.display = 'none';
            photo.style.display = 'block';
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('retakeBtn').style.display = 'block';
            document.getElementById('submitBtn').style.display = 'block';
            
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
        
        document.getElementById('retakeBtn').addEventListener('click', () => {
            document.getElementById('photo').style.display = 'none';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('captureBtn').style.display = 'block';
            startCamera();
        });
        
        document.getElementById('submitBtn').addEventListener('click', async () => {
            if (!scannedUuid || !photoData) {
                showError('Missing UUID or photo');
                return;
            }
            
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Processing...';
            
            try {
                const response = await fetch('{{ route("kiosk.scan", $kiosk->token) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        uuid: scannedUuid,
                        photo: photoData
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Check-in successful!');
                    window.location.href = '{{ route("kiosk.index", $kiosk->token) }}';
                } else {
                    showError(data.error || 'Check-in failed');
                    document.getElementById('submitBtn').disabled = false;
                    document.getElementById('submitBtn').textContent = 'Complete Check-in';
                }
            } catch (err) {
                showError('Network error: ' + err.message);
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('submitBtn').textContent = 'Complete Check-in';
            }
        });
        
        function showError(msg) {
            document.getElementById('errorMsg').textContent = msg;
            document.getElementById('error').classList.remove('hidden');
        }
    </script>
</body>
</html>
