const REQUEST_DELAY = 250; // ms: request Livewire yang lebih cepat dari ini tidak menampilkan bar
const SAFETY_MS = 20000; // bar selalu selesai sendiri, mis. bila tautan ternyata mengunduh file
const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

const root = () => document.getElementById("top-progress");
const fill = () => root()?.firstElementChild;

let running = false;
let navigating = false;
let value = 0;
let trickleTimer = null;
let safetyTimer = null;
let hideTimer = null;
let inflight = 0;
let delayTimer = null;

function paint(v) {
    const el = fill();
    if (el) el.style.transform = `scaleX(${v})`;
}

function start() {
    const host = root();
    const el = fill();
    if (!host || !el || running) return;

    running = true;
    clearTimeout(hideTimer);

    // Kembali ke 0 tanpa animasi, lalu mulai.
    el.style.transition = "none";
    paint(0);
    void el.offsetWidth;
    el.style.transition = "";

    host.style.opacity = "1";
    value = reduceMotion.matches ? 0.6 : 0.08;
    paint(value);

    if (!reduceMotion.matches) {
        trickleTimer = setInterval(() => {
            value += (0.95 - value) * (0.04 + Math.random() * 0.08);
            paint(value);
        }, 300);
    }

    clearTimeout(safetyTimer);
    safetyTimer = setTimeout(finishNavigation, SAFETY_MS);
}

function finish() {
    if (!running) return;

    running = false;
    clearInterval(trickleTimer);
    clearTimeout(safetyTimer);
    paint(1);

    hideTimer = setTimeout(() => {
        const host = root();
        if (host) host.style.opacity = "0";
    }, 200);
}

function startNavigation() {
    navigating = true;
    start();
}

function finishNavigation() {
    navigating = false;
    finish();
}

// ---- Pindah halaman biasa (full page load) ----

document.addEventListener("click", (e) => {
    if (
        e.defaultPrevented ||
        e.button !== 0 ||
        e.metaKey ||
        e.ctrlKey ||
        e.shiftKey ||
        e.altKey
    )
        return;

    const a = e.target.closest?.("a[href]");
    if (
        !a ||
        (a.target && a.target !== "_self") ||
        a.hasAttribute("download") ||
        a.hasAttribute("data-no-progress")
    )
        return;

    // wire:navigate ditangani lewat event livewire:navigate di bawah.
    if ([...a.attributes].some((attr) => attr.name.startsWith("wire:navigate")))
        return;

    let url;
    try {
        url = new URL(a.href, location.href);
    } catch {
        return;
    }

    if (!/^https?:$/.test(url.protocol) || url.origin !== location.origin)
        return;

    // Tautan #anchor di halaman yang sama tidak memuat ulang halaman.
    if (
        url.hash &&
        url.pathname === location.pathname &&
        url.search === location.search
    )
        return;

    startNavigation();
});

document.addEventListener("submit", (e) => {
    const form = e.target;

    // Form wire:submit sudah di-preventDefault oleh Livewire, jadi tidak lolos ke sini.
    if (e.defaultPrevented || !(form instanceof HTMLFormElement)) return;
    if (
        form.method === "dialog" ||
        (form.target && form.target !== "_self") ||
        e.submitter?.formTarget === "_blank"
    )
        return;

    startNavigation();
});

// Reload, URL diketik, tombol back/forward.
window.addEventListener("beforeunload", startNavigation);

// Kembali lewat bfcache: halaman dipulihkan apa adanya, jadi bar harus direset.
window.addEventListener("pageshow", (e) => {
    if (e.persisted) finishNavigation();
});

// ---- wire:navigate ----

document.addEventListener("livewire:navigate", startNavigation);
document.addEventListener("livewire:navigated", finishNavigation);

// ---- Request Livewire (aksi, wire:model.live, polling) ----

document.addEventListener("livewire:init", () => {
    window.Livewire.hook("request", ({ respond, fail }) => {
        inflight++;
        if (inflight === 1) delayTimer = setTimeout(start, REQUEST_DELAY);

        let finished = false;
        const end = () => {
            if (finished) return;
            finished = true;
            inflight = Math.max(0, inflight - 1);

            if (inflight === 0) {
                clearTimeout(delayTimer);
                if (!navigating) finish();
            }
        };

        respond(end);

        fail(({ status, preventDefault }) => {
            end();

            const messages = {
                403: "You don’t have permission to do that.",
                419: "Your session expired. Reload the page and try again.",
                429: "Too many attempts. Wait a moment and try again.",
            };

            if (messages[status]) {
                preventDefault?.(); // gantikan modal/konfirmasi bawaan Livewire dengan toast
                window.toast?.({ type: "danger", message: messages[status] });
            }
        });
    });
});

window.TopProgress = { start: startNavigation, done: finishNavigation };
