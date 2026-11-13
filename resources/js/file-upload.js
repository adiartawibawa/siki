const UNITS = ["B", "KB", "MB", "GB"];

function formatBytes(bytes) {
    let n = bytes;
    let i = 0;

    while (n >= 1024 && i < UNITS.length - 1) {
        n /= 1024;
        i++;
    }

    return `${n >= 10 || i === 0 ? Math.round(n) : n.toFixed(1)} ${UNITS[i]}`;
}

/** Cocokkan berkas dengan atribut accept: "image/*", "image/png", atau ".pdf". Hanya saran; server tetap memeriksa. */
function allowed(file, accept) {
    const tokens = (accept || "")
        .split(",")
        .map((t) => t.trim().toLowerCase())
        .filter(Boolean);
    if (!tokens.length) return true;

    const name = file.name.toLowerCase();
    const type = (file.type || "").toLowerCase();

    return tokens.some((t) =>
        t.startsWith(".")
            ? name.endsWith(t)
            : t.endsWith("/*")
              ? type.startsWith(t.slice(0, -1))
              : type === t,
    );
}

document.addEventListener("alpine:init", () => {
    window.Alpine.data(
        "fileUpload",
        ({ model, accept, maxBytes, disabled }) => ({
            dragging: false,
            item: null, // { name, size, preview, progress, status: 'uploading' | 'done' | 'error', message }
            notice: "", // alasan penolakan di sisi klien

            pick(files) {
                if (disabled || !files || !files.length) return;

                // Komponen ini satu berkas: yang lain diabaikan dan pengguna diberi tahu.
                const file = files[0];
                this.notice =
                    files.length > 1
                        ? "Only one file can be uploaded. The first one was used."
                        : "";

                if (!allowed(file, accept)) {
                    this.notice = "This file type is not allowed.";
                    return;
                }

                if (file.size > maxBytes) {
                    this.notice = `This file is too large (${formatBytes(file.size)}). The limit is ${formatBytes(maxBytes)}.`;
                    return;
                }

                this.discard(false);

                this.item = {
                    name: file.name,
                    size: formatBytes(file.size),
                    preview: file.type.startsWith("image/")
                        ? URL.createObjectURL(file)
                        : null,
                    progress: 0,
                    status: "uploading",
                    message: "",
                };

                this.$wire.upload(
                    model,
                    file,
                    () => {
                        if (!this.item) return;
                        this.item.progress = 100;
                        this.item.status = "done";
                    },
                    () => {
                        if (!this.item) return;
                        this.item.status = "error";
                        this.item.message =
                            "Upload failed. Check the file and try again.";
                    },
                    (event) => {
                        if (this.item)
                            this.item.progress = event.detail.progress;
                    },
                );
            },

            remove() {
                this.discard(true);
                this.notice = "";
            },

            /** Buang pilihan: batalkan unggahan berjalan, lepas URL pratinjau, dan (opsional) kosongkan properti server. */
            discard(clearServer) {
                if (!this.item) return;

                if (this.item.status === "uploading")
                    this.$wire.cancelUpload(model);
                if (this.item.preview) URL.revokeObjectURL(this.item.preview);

                this.item = null;

                if (clearServer) this.$wire.$set(model, null, false);
            },

            destroy() {
                if (this.item?.preview) URL.revokeObjectURL(this.item.preview);
            },
        }),
    );
});
