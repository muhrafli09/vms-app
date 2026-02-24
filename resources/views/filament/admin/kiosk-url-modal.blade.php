<div class="p-4">
    <div class="space-y-4">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                Use this URL to access the kiosk interface:
            </p>
            <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg">
                <code class="text-sm break-all">
                    {{ url('/kiosk/' . $kiosk->token) }}
                </code>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button 
                type="button"
                onclick="navigator.clipboard.writeText('{{ url('/kiosk/' . $kiosk->token) }}')"
                class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
            >
                📋 Copy URL
            </button>
        </div>
        
        <div class="border-t pt-4 mt-4">
            <h4 class="font-semibold mb-2">Kiosk Details</h4>
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="text-gray-600 dark:text-gray-400">Name:</dt>
                    <dd class="font-medium">{{ $kiosk->name }}</dd>
                </div>
                @if($kiosk->location)
                <div>
                    <dt class="text-gray-600 dark:text-gray-400">Location:</dt>
                    <dd class="font-medium">{{ $kiosk->location }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-gray-600 dark:text-gray-400">Status:</dt>
                    <dd>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $kiosk->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $kiosk->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
