<div class="space-y-4" wire:poll.2s>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</h3>
            <p class="mt-1">
                <span @class([
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' => $deployment->status === 'running',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $deployment->status === 'completed',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $deployment->status === 'failed',
                ])>
                    {{ ucfirst($deployment->status) }}
                </span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">User</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $deployment->user->name }}
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Created At</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $deployment->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Completed At</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $deployment->completed_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Exit Code</h3>
            <p class="mt-1">
                <span @class([
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $deployment->exit_code === 0,
                    'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' => $deployment->exit_code === null,
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $deployment->exit_code !== 0 && $deployment->exit_code !== null,
                ])>
                    {{ $deployment->exit_code ?? 'N/A' }}
                </span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Duration</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                @if($deployment->created_at && $deployment->completed_at)
                    {{ $deployment->created_at->diffForHumans($deployment->completed_at, true) }}
                @else
                    N/A
                @endif
            </p>
        </div>
    </div>

    @if($deployment->output)
        <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Output</h3>
                @if($deployment->status === 'running')
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">
                        <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Live
                    </span>
                @endif
            </div>
            <div
                id="deployment-output"
                class="bg-gray-50 dark:bg-gray-900 rounded-lg overflow-x-auto max-h-96 overflow-y-auto"
                x-data="{
                    autoScroll: true,
                    scrollToBottom() {
                        if (this.autoScroll) {
                            this.$el.scrollTop = this.$el.scrollHeight;
                        }
                    }
                }"
                x-init="
                    scrollToBottom();
                    $watch('$el.scrollHeight', () => scrollToBottom());
                "
                @scroll="autoScroll = ($el.scrollHeight - $el.scrollTop - $el.clientHeight) < 50"
            >
                <pre class="text-xs text-gray-900 dark:text-gray-100 whitespace-pre-wrap font-mono">{{ $deployment->output }}</pre>
            </div>
        </div>
    @endif

    @if($deployment->error_output)
        <div class="mt-4">
            <h3 class="text-sm font-medium text-red-700 dark:text-red-300 mb-2">Error Output</h3>
            <div
                id="deployment-error-output"
                class="bg-red-50 dark:bg-red-900/20 rounded-lg overflow-x-auto max-h-96 overflow-y-auto"
                x-data="{
                    autoScroll: true,
                    scrollToBottom() {
                        if (this.autoScroll) {
                            this.$el.scrollTop = this.$el.scrollHeight;
                        }
                    }
                }"
                x-init="
                    scrollToBottom();
                    $watch('$el.scrollHeight', () => scrollToBottom());
                "
                @scroll="autoScroll = ($el.scrollHeight - $el.scrollTop - $el.clientHeight) < 50"
            >
                <pre class="text-xs text-red-900 dark:text-red-100 whitespace-pre-wrap font-mono">{{ $deployment->error_output }}</pre>
            </div>
        </div>
    @endif
</div>
