<div x-data="{
    streaming: false,
    captured: false,
    preview: null,
    photoData: null,
    stream: null,
    
    async startCamera() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            this.$refs.video.srcObject = this.stream;
            this.$refs.video.play();
            this.streaming = true;
        } catch (err) {
            alert('Unable to access camera: ' + err.message);
        }
    },
    
    capture() {
        const video = this.$refs.video;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        this.preview = canvas.toDataURL('image/jpeg');
        this.photoData = this.preview;
        this.captured = true;
        this.stopCamera();
    },
    
    retake() {
        this.captured = false;
        this.preview = null;
        this.photoData = null;
    },
    
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        this.streaming = false;
    }
}" class="space-y-2">
    <div class="relative">
        <video x-ref="video" x-show="streaming" class="w-full rounded-lg border" style="max-height: 300px;"></video>
        <img x-show="captured" :src="preview" class="w-full rounded-lg border" style="max-height: 300px;">
    </div>
    
    <input type="hidden" wire:model="{{ $getStatePath() }}" x-model="photoData">
    
    <div class="flex gap-2">
        <button 
            type="button"
            x-show="!streaming && !captured"
            @click="startCamera"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Open Camera
        </button>
        
        <button 
            type="button"
            x-show="streaming"
            @click="capture"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Take Photo
        </button>
        
        <button 
            type="button"
            x-show="captured"
            @click="retake"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
            Retake
        </button>
        
        <button 
            type="button"
            x-show="streaming"
            @click="stopCamera"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Cancel
        </button>
    </div>
</div>
