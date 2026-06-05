<x-app-layout>
    @section('page-title', 'Tableaux')
    @php
        $canManageBoards = auth()->user()?->can('create', \App\Models\Board::class);
    @endphp
    @section('topbar-action')
        @if($canManageBoards)
            <button type="button" class="btn-primary" onclick="document.getElementById('create-board-modal').showModal()">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2v10M2 7h10" stroke="white" stroke-width="1.8" stroke-linecap="round"/></svg>
                Nouveau tableau
            </button>
        @endif
    @endsection

    <livewire:boards.board-index />

    @push('scripts')
    <script>
        (() => {
            const setupDialogs = () => {
                document.querySelectorAll('dialog').forEach((dialog) => {
                    dialog.addEventListener('click', (event) => {
                        const rect = dialog.getBoundingClientRect();
                        const inside =
                            event.clientX >= rect.left &&
                            event.clientX <= rect.right &&
                            event.clientY >= rect.top &&
                            event.clientY <= rect.bottom;

                        if (!inside) {
                            dialog.close();
                        }
                    });
                });
            };
            setupDialogs();
            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updated', (el, component) => {
                    setupDialogs();
                });
            });
        })();
    </script>
    @endpush
</x-app-layout>
