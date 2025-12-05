<div class="space-y-4">
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
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Output</h3>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto">
                <pre class="text-xs text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $deployment->output }}</pre>
            </div>
        </div>
    @endif

    @if($deployment->error_output)
        <div class="mt-4">
            <h3 class="text-sm font-medium text-red-700 dark:text-red-300 mb-2">Error Output</h3>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 overflow-x-auto">
                <pre class="text-xs text-red-900 dark:text-red-100 whitespace-pre-wrap">{{ $deployment->error_output }}</pre>
            </div>
        </div>
    @endif
</div>
