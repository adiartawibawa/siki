const DEFAULT_MS = { success: 5000, info: 5000, warning: 8000, danger: 0 }; // 0 = menetap
const TYPES = Object.keys(DEFAULT_MS);

// Dari JS mana pun: window.toast({ type: 'success', message: 'Saved.' })
window.toast = (detail) =>
    window.dispatchEvent(new CustomEvent("toast", { detail }));

document.addEventListener("alpine:init", () => {
    window.Alpine.data("toaster", (flash = []) => ({
        items: [],
        seq: 0,
        max: 4,

        init() {
            flash.forEach((t) => this.add(t));
        },

        add(raw) {
            // Livewire mengirim detail berupa objek (argumen bernama) atau array (posisional).
            const d = Array.isArray(raw) ? (raw[0] ?? {}) : (raw ?? {});
            const message = String(d.message ?? "").trim();
            if (!message) return;

            const type = TYPES.includes(d.type) ? d.type : "info";
            const duration = Number.isFinite(d.duration)
                ? d.duration
                : DEFAULT_MS[type];

            // Pesan yang sama tidak ditumpuk, cukup perpanjang.
            const same = this.items.find(
                (t) => t.type === type && t.message === message && !t.leaving,
            );
            if (same) {
                same.remaining = same.duration;
                this.schedule(same);
                return;
            }

            this.items.push({
                id: ++this.seq,
                type,
                message,
                title: d.title || null,
                duration,
                remaining: duration,
                deadline: 0,
                timer: null,
                leaving: false,
            });

            const toast = this.items[this.items.length - 1];
            this.schedule(toast);

            while (this.items.length > this.max) this.remove(this.items[0].id);
        },

        schedule(t) {
            clearTimeout(t.timer);
            t.timer = null;
            if (!t.duration) return;

            t.deadline = Date.now() + t.remaining;
            t.timer = setTimeout(() => this.dismiss(t), t.remaining);
        },

        pause(t) {
            if (!t.timer) return;
            clearTimeout(t.timer);
            t.timer = null;
            t.remaining = Math.max(1500, t.deadline - Date.now());
        },

        resume(t) {
            if (t.duration && !t.timer && !t.leaving) this.schedule(t);
        },

        dismiss(t) {
            if (t.leaving) return;
            clearTimeout(t.timer);
            t.timer = null;
            t.leaving = true;
            setTimeout(() => this.remove(t.id), 200);
        },

        remove(id) {
            const i = this.items.findIndex((t) => t.id === id);
            if (i === -1) return;
            clearTimeout(this.items[i].timer);
            this.items.splice(i, 1);
        },
    }));
});
