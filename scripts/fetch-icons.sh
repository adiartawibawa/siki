#!/usr/bin/env bash
# Mengunduh ikon Lucide ke resources/svg/lucide
# Pemakaian: ./scripts/fetch-icons.sh check trash-2 user save loader-circle

set -euo pipefail

DEST="resources/svg/lucide"
BASE="https://raw.githubusercontent.com/lucide-icons/lucide/main/icons"

mkdir -p "$DEST"

for name in "$@"; do
    # -f: gagal jika ikon tidak ada (404), -sS: senyap tapi tampilkan error
    if curl -fsS "$BASE/$name.svg" -o "$DEST/$name.svg"; then
        echo "✔ $name"
    else
        echo "✘ $name (tidak ditemukan)"
    fi
done
