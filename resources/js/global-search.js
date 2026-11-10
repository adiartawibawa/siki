const MIN_LENGTH = 2;

document.addEventListener("alpine:init", () => {
    window.Alpine.data("globalSearch", (url) => ({
        q: "",
        open: false,
        loading: false,
        failed: false,
        groups: [],
        active: -1,
        controller: null,

        get term() {
            return this.q.trim();
        },

        get ready() {
            return this.term.length >= MIN_LENGTH;
        },

        get flat() {
            return this.groups.flatMap((g) => g.items);
        },

        async search() {
            // Permintaan sebelumnya dibatalkan, jadi jawaban lama tidak bisa menimpa yang baru.
            this.controller?.abort();
            this.active = -1;
            this.failed = false;

            if (!this.ready) {
                this.groups = [];
                this.loading = false;
                return;
            }

            this.controller = new AbortController();
            this.loading = true;

            try {
                const res = await fetch(
                    `${url}?q=${encodeURIComponent(this.term)}`,
                    {
                        headers: { Accept: "application/json" },
                        signal: this.controller.signal,
                    },
                );

                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                let n = 0;
                this.groups = (await res.json()).groups.map((g) => ({
                    ...g,
                    items: g.items.map((item) => ({ ...item, n: n++ })),
                }));
                this.loading = false;
            } catch (e) {
                if (e.name === "AbortError") return;

                this.groups = [];
                this.failed = true;
                this.loading = false;
            }
        },

        move(step) {
            const total = this.flat.length;
            if (!total) return;

            this.active =
                this.active === -1
                    ? step > 0
                        ? 0
                        : total - 1
                    : (this.active + step + total) % total;
            document
                .getElementById(`gs-${this.active}`)
                ?.scrollIntoView({ block: "nearest" });
        },

        // Enter tanpa sorotan memilih hasil pertama.
        choose() {
            const item = this.flat[Math.max(this.active, 0)];
            if (item) this.go(item);
        },

        go(item) {
            this.open = false;
            window.Livewire?.navigate
                ? window.Livewire.navigate(item.url)
                : (window.location.href = item.url);
        },

        // Ctrl/Cmd/Shift-klik dan klik tengah dibiarkan membuka tab baru.
        click(event, item) {
            if (
                event.button !== 0 ||
                event.metaKey ||
                event.ctrlKey ||
                event.shiftKey
            )
                return;

            event.preventDefault();
            this.go(item);
        },
    }));
});
