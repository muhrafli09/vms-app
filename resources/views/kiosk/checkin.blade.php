<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in - {{ $kiosk->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-center mb-6">Visitor Check-in</h1>
            
            @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
            @endif
            
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                @foreach($errors->all() as $error)
                    <p class="text-red-800">{{ $error }}</p>
                @endforeach
            </div>
            @endif
            
            <form id="checkinForm" action="{{ route('kiosk.checkin.post', $kiosk->token) }}" method="POST">
                @csrf
                
                <div id="formFields" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name *</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg" value="{{ old('name') }}">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone *</label>
                        <input type="tel" name="phone" required class="w-full px-4 py-2 border rounded-lg" value="{{ old('phone') }}">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" class="w-full px-4 py-2 border rounded-lg" value="{{ old('email') }}">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Company</label>
                        <input type="text" name="company" class="w-full px-4 py-2 border rounded-lg" value="{{ old('company') }}">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Meeting with *</label>
                        <select name="employee_id" required class="w-full px-4 py-2 border rounded-lg">
                            <option value="">Select employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Purpose</label>
                        <textarea name="purpose" rows="3" class="w-full px-4 py-2 border rounded-lg">{{ old('purpose') }}</textarea>
                    </div>
                    
                    <div class="pt-4">
                        <button type="button" id="nextBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                            Next: Take Photo
                        </button>
                    </div>
                </div>
                
                <div id="photoSection" class="hidden">
                    <div class="mb-4">
                        <p class="text-center text-gray-600 mb-4">Please take a selfie</p>
                        <video id="video" autoplay class="w-full rounded-lg border mb-4" style="display:none;"></video>
                        <canvas id="canvas" style="display:none;"></canvas>
                        <img id="photo" class="w-full rounded-lg border mb-4" style="display:none;">
                    </div>
                    
                    <input type="hidden" name="photo" id="photoInput">
                    
                    <div class="space-y-2">
                        <button type="button" id="captureBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                            Take Photo
                        </button>
                        <button type="button" id="retakeBtn" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg" style="display:none;">
                            Retake Photo
                        </button>
                        <button type="submit" id="submitBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg" style="display:none;">
                            Complete Check-in
                        </button>
                        <button type="button" id="backBtn" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg">
                            Back to Form
                        </button>
                    </div>
                </div>
                
                <div class="flex gap-4 pt-4">
                    <a href="{{ route('kiosk.index', $kiosk->token) }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let stream = null;
        
        document.getElementById('nextBtn').addEventListener('click', () => {
            const form = document.getElementById('checkinForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            document.getElementById('formFields').classList.add('hidden');
            document.getElementById('photoSection').classList.remove('hidden');
            startCamera();
        });
        
        document.getElementById('backBtn').addEventListener('click', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            document.getElementById('photoSection').classList.add('hidden');
            document.getElementById('formFields').classList.remove('hidden');
        });
        
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                const video = document.getElementById('video');
                video.srcObject = stream;
                video.style.display = 'block';
            } catch (err) {
                alert('Camera access denied');
            }
        }
        
        document.getElementById('captureBtn').addEventListener('click', () => {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const photo = document.getElementById('photo');
            const photoInput = document.getElementById('photoInput');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            const photoData = canvas.toDataURL('image/jpeg');
            photo.src = photoData;
            photoInput.value = photoData;
            
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
        
        document.getElementById('checkinForm').addEventListener('submit', (e) => {
            const photoInput = document.getElementById('photoInput');
            if (!photoInput.value) {
                e.preventDefault();
                alert('Please take a photo before submitting');
            }
        });
    </script>
</body>
</html>
