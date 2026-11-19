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
        let after = Number(feed.dataset.liveAfter || 0);
        let busy = false;

        const poll = async () => {
            if (busy || document.hidden) {
                return;
            }

            busy = true;
            try {
                const response = await fetch(`${feed.dataset.liveEndpoint}?after=${after}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                after = Math.max(after, Number(data.latestId || 0));
                feed.dataset.liveAfter = String(after);

                const list = feed.querySelector('[data-live-list]');
                if (list && typeof data.html === 'string' && data.html.trim()) {
                    list.querySelector('[data-live-empty]')?.remove();
                    list.insertAdjacentHTML('afterbegin', data.html);

                    const items = list.querySelectorAll('[data-live-item]');
                    if (items.length > 12) {
                        items[items.length - 1].remove();
                    }
                }

                Object.entries(data.counters || {}).forEach(([key, value]) => {
                    document.querySelectorAll(`[data-live-counter="${key}"]`).forEach((element) => {
                        element.textContent = String(value);
                    });
                });
            } catch {
            } finally {
                busy = false;
            }
        };

        window.setInterval(poll, 5000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                poll();
            }
        });
    });
});
