document.addEventListener("alpine:init", () => {
    window.Alpine.data(
        "notifications",
        ({ listUrl, readAllUrl, csrf, unread }) => ({
            open: false,
            unread,
            items: [],
            loading: false,
            loaded: false,
            failed: false,

            async toggle() {
                this.open = !this.open;
                if (this.open) await this.load();
            },

            async load() {
                this.loading = true;
                this.failed = false;

                try {
                    const res = await fetch(listUrl, {
                        headers: { Accept: "application/json" },
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const data = await res.json();
                    this.items = data.items;
                    this.unread = data.unread;
                    this.loaded = true;
                } catch {
                    this.failed = true;
                } finally {
                    this.loading = false;
                }
            },

            async readAll() {
                this.failed = false;

                const res = await fetch(readAllUrl, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                });

                if (!res.ok) {
                    this.failed = true;
                    return;
                }

                this.items = this.items.map((item) => ({
                    ...item,
                    read: true,
                }));
                this.unread = 0;
            },
        }),
    );
});
