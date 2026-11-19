import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert-auto-dismiss').forEach((alert) => {
        window.setTimeout(() => {
            if (alert && alert.parentNode) {
                const instance = bootstrap.Alert.getOrCreateInstance(alert);
                instance.close();
            }
        }, 5000);
    });

    document.querySelectorAll('[data-live-feed]').forEach((feed) => {
        const endpoint = feed.dataset.liveEndpoint;
        const list = feed.querySelector('[data-live-list]');
        if (!endpoint || !list) return;

        const seenExits = new Set();

        const poll = async () => {
            const after = feed.dataset.liveAfter || 0;
            const params = new URLSearchParams({
                after: String(after),
                seen_exits: Array.from(seenExits).join(','),
            });

            const res = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const data = await res.json();

            if (Array.isArray(data.exitIds)) {
                data.exitIds.forEach((id) => seenExits.add(Number(id)));
            }

            if (data.html) {
                const temp = document.createElement('div');
                temp.innerHTML = data.html;

                temp.querySelectorAll('[data-live-item]').forEach((incoming) => {
                    const id = incoming.getAttribute('data-live-item');
                    const existing = list.querySelector(`[data-live-item="${id}"]`);

                    // Remove any accidental duplicates of this ID first
                    list.querySelectorAll(`[data-live-item="${id}"]`).forEach((el) => {
                        if (el !== existing) el.remove();
                    });

                    if (existing) {
                        existing.replaceWith(incoming);
                    } else {
                        list.querySelector('[data-live-empty]')?.remove();
                        list.prepend(incoming);
                    }
                });
            }

            if (typeof data.latestId !== 'undefined') {
                feed.dataset.liveAfter = data.latestId;
            }

            if (data.counters) {
                Object.entries(data.counters).forEach(([key, value]) => {
                    const el = document.querySelector(`[data-live-counter="${key}"]`);
                    if (el) el.textContent = value;
                });
            }
        };

        setInterval(poll, 5000);
    });
});
