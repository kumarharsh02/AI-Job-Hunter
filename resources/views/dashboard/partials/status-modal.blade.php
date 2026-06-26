<div id="statusModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeStatusModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title">
                    Update Application Status
                </h3>
                <div class="mt-5">
                    <form id="statusForm" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-2">
                            @foreach(\App\Models\Application::statuses() as $statusValue => $statusLabel)
                                @php
                                    $colors = \App\Models\Application::statusColors();
                                    $color = $colors[$statusValue] ?? 'gray';
                                    $ringColor = match($color) {
                                        'green' => 'ring-emerald-500',
                                        'blue' => 'ring-blue-500',
                                        'red' => 'ring-red-500',
                                        'yellow' => 'ring-amber-500',
                                        'indigo' => 'ring-indigo-500',
                                        default => 'ring-slate-500'
                                    };
                                @endphp
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-indigo-300 has-[:checked]:bg-indigo-50">
                                    <input type="radio" name="status" value="{{ $statusValue }}"
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                           data-status-value="{{ $statusValue }}">
                                    <span class="text-sm font-medium text-slate-700">{{ $statusLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3 rounded-b-2xl">
                <button type="button" onclick="document.getElementById('statusForm').submit()"
                        class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-base font-medium text-white hover:from-indigo-700 hover:to-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition-all">
                    Update
                </button>
                <button type="button" onclick="closeStatusModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openStatusModal(applicationId, currentStatus) {
        const modal = document.getElementById('statusModal');
        const form = document.getElementById('statusForm');
        form.action = '/applications/' + applicationId + '/status';

        const radios = form.querySelectorAll('input[name="status"]');
        radios.forEach(radio => {
            radio.checked = (radio.value === currentStatus);
        });

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeStatusModal() {
        const modal = document.getElementById('statusModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeStatusModal();
    });
</script>