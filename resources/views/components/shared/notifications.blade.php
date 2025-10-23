{{-- Notification Container --}}
<div id="notifications" class="fixed top-4 right-4 z-50 space-y-2" aria-live="polite" aria-label="{{ __('Notifications') }}"></div>

{{-- Notification Handler Script attaches to the Livewire notify event stream. --}}
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('livewire:init', () => {
        Livewire.on('notify', (event) => {
            const notification = event[0] || event;
            const container = document.getElementById('notifications');

            if (! container || ! notification) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'bg-slate-900/90 text-white px-4 py-3 rounded-lg shadow-lg flex items-start gap-3';
            wrapper.setAttribute('role', 'status');

            const message = document.createElement('div');
            message.className = 'text-sm leading-5';
            message.innerHTML = notification.message ?? '';

            wrapper.appendChild(message);
            container.appendChild(wrapper);

            setTimeout(() => {
                wrapper.classList.add('opacity-0', 'transition');
                setTimeout(() => wrapper.remove(), 300);
            }, notification.duration ?? 4000);
        });
    });
</script>
