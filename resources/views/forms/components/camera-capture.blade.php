<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $fieldId = 'camera_' . str_replace(['.', '-'], '_', $getId());
    @endphp
    
    <div class="space-y-2">
        <div class="relative">
            <video id="{{ $fieldId }}_video" class="w-full rounded-lg border" style="max-height: 300px; display: none;"></video>
            <canvas id="{{ $fieldId }}_canvas" style="display: none;"></canvas>
            <img id="{{ $fieldId }}_photo" class="w-full rounded-lg border" style="max-height: 300px; display: none;">
        </div>
        
        <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}" id="{{ $fieldId }}_input">
        
        <div class="flex gap-2">
            <button 
                type="button"
                id="{{ $fieldId }}_openBtn"
                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Open Camera
            </button>
            
            <button 
                type="button"
                id="{{ $fieldId }}_captureBtn"
                style="display: none;"
                class="px-4 py-2 bg-success-600 text-white rounded-lg hover:bg-success-700">
                Take Photo
            </button>
            
            <button 
                type="button"
                id="{{ $fieldId }}_retakeBtn"
                style="display: none;"
                class="px-4 py-2 bg-warning-600 text-white rounded-lg hover:bg-warning-700">
                Retake
            </button>
            
            <button 
                type="button"
                id="{{ $fieldId }}_cancelBtn"
                style="display: none;"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Cancel
            </button>
        </div>
    </div>
    
    <script>
    (function() {
        let stream_{{ $fieldId }} = null;
        
        document.getElementById('{{ $fieldId }}_openBtn').addEventListener('click', async function() {
            try {
                const video = document.getElementById('{{ $fieldId }}_video');
                const openBtn = document.getElementById('{{ $fieldId }}_openBtn');
                const captureBtn = document.getElementById('{{ $fieldId }}_captureBtn');
                const cancelBtn = document.getElementById('{{ $fieldId }}_cancelBtn');
                
                stream_{{ $fieldId }} = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                video.srcObject = stream_{{ $fieldId }};
                video.style.display = 'block';
                openBtn.style.display = 'none';
                captureBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';
            } catch (err) {
                alert('Unable to access camera: ' + err.message);
            }
        });
        
        document.getElementById('{{ $fieldId }}_captureBtn').addEventListener('click', function() {
            const video = document.getElementById('{{ $fieldId }}_video');
            const canvas = document.getElementById('{{ $fieldId }}_canvas');
            const photo = document.getElementById('{{ $fieldId }}_photo');
            const input = document.getElementById('{{ $fieldId }}_input');
            const captureBtn = document.getElementById('{{ $fieldId }}_captureBtn');
            const cancelBtn = document.getElementById('{{ $fieldId }}_cancelBtn');
            const retakeBtn = document.getElementById('{{ $fieldId }}_retakeBtn');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            const photoData = canvas.toDataURL('image/jpeg');
            photo.src = photoData;
            input.value = photoData;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            
            if (stream_{{ $fieldId }}) {
                stream_{{ $fieldId }}.getTracks().forEach(track => track.stop());
                stream_{{ $fieldId }} = null;
            }
            video.style.display = 'none';
            video.srcObject = null;
            
            photo.style.display = 'block';
            captureBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
        });
        
        document.getElementById('{{ $fieldId }}_retakeBtn').addEventListener('click', function() {
            const photo = document.getElementById('{{ $fieldId }}_photo');
            const input = document.getElementById('{{ $fieldId }}_input');
            const retakeBtn = document.getElementById('{{ $fieldId }}_retakeBtn');
            const openBtn = document.getElementById('{{ $fieldId }}_openBtn');
            
            photo.style.display = 'none';
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            retakeBtn.style.display = 'none';
            openBtn.style.display = 'inline-block';
        });
        
        document.getElementById('{{ $fieldId }}_cancelBtn').addEventListener('click', function() {
            const video = document.getElementById('{{ $fieldId }}_video');
            const captureBtn = document.getElementById('{{ $fieldId }}_captureBtn');
            const cancelBtn = document.getElementById('{{ $fieldId }}_cancelBtn');
            const openBtn = document.getElementById('{{ $fieldId }}_openBtn');
            
            if (stream_{{ $fieldId }}) {
                stream_{{ $fieldId }}.getTracks().forEach(track => track.stop());
                stream_{{ $fieldId }} = null;
            }
            video.style.display = 'none';
            video.srcObject = null;
            
            captureBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            openBtn.style.display = 'inline-block';
        });
    })();
    </script>
</x-dynamic-component>
