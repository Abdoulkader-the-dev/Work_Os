// resources/js/app.js

import './bootstrap';
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';

// ── Flatpickr locale globale ──────────────────────────────
flatpickr.localize(French);
window.flatpickr = flatpickr;

// ── Alpine.js ────────────────────────────────────────────
window.Alpine = Alpine;
Alpine.start();

// ── SortableJS global ────────────────────────────────────
window.Sortable = Sortable;

// ── Kanban drag & drop ───────────────────────────────────
function initKanban() {
    document.querySelectorAll('.kanban-column').forEach(col => {
        if (col._sortable) {
            col._sortable.destroy();
            col._sortable = null;
        }
        col._sortable = Sortable.create(col, {
            group:            'kanban',
            animation:        180,
            delay:            80,
            delayOnTouchOnly: true,
            ghostClass:       'kanban-ghost',
            dragClass:        'kanban-dragging',
            onStart() {
                // Highlight colonnes cibles
                document.querySelectorAll('.kanban-column').forEach(c => {
                    c.style.background    = 'rgba(0,145,205,0.02)';
                    c.style.borderRadius  = '10px';
                    c.style.transition    = 'background .2s';
                });
            },
            onEnd(evt) {
                // Reset highlight
                document.querySelectorAll('.kanban-column').forEach(c => {
                    c.style.background = '';
                });

                const itemId = parseInt(evt.item.dataset.itemId);
                const status = evt.to.dataset.status;
                const order  = evt.newIndex;

                if (!itemId || !status) return;

                // Dispatcher vers Livewire
                Livewire.dispatch('item-moved', { itemId, status, order });
            }
        });
    });
}

// Init et re-init après chaque render Livewire
document.addEventListener('livewire:initialized',  initKanban);
document.addEventListener('livewire:updated',      initKanban);