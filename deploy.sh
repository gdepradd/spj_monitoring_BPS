#!/bin/bash
#
# Deploy SPJ Monitoring ke hosting bps-batanghari.com.
#
# Jalankan dari root project di LOKAL:  ./deploy.sh
#
# Catatan penting soal server:
#   - Tidak ada Node/npm di sana, jadi aset harus di-build di lokal lebih dulu.
#   - index.php berada di ROOT aplikasi (bukan di public/), hasil penyesuaian
#     shared hosting. Karena itu file tersebut, .env, dan storage/ TIDAK pernah
#     ikut dikirim — kalau tertimpa versi repo, routing di server rusak.
#   - --delete hanya dipakai untuk public/build, karena nama file hasil build
#     berubah setiap kali sehingga file lama harus dibersihkan.
#
set -euo pipefail

SRV="u927936405@46.202.138.202"
DIR="public_html/SiMoncer"
SSH="ssh -p 65002"
URL="https://bps-batanghari.com/SiMoncer"

echo "[1/4] build aset di lokal..."
# Repo ini tersimpan di drive exFAT/NTFS yang tidak mendukung symlink, sehingga
# node_modules/.bin/vite bisa berupa file shortcut Windows dan gagal dieksekusi
# ("Exec format error"). Bila itu terjadi, vite dipanggil langsung lewat node.
if ! npm run build; then
    echo "  npm run build gagal — memanggil vite langsung lewat node..."
    node node_modules/vite/bin/vite.js build
fi

echo "[2/4] kirim kode aplikasi..."
rsync -avz -e "$SSH" app/                    "$SRV:$DIR/app/"
rsync -avz -e "$SSH" routes/                 "$SRV:$DIR/routes/"
rsync -avz -e "$SSH" resources/              "$SRV:$DIR/resources/"
rsync -avz -e "$SSH" config/                 "$SRV:$DIR/config/"
rsync -avz -e "$SSH" database/migrations/    "$SRV:$DIR/database/migrations/"
rsync -avz -e "$SSH" database/seeders/       "$SRV:$DIR/database/seeders/"
rsync -avz -e "$SSH" public/aset/            "$SRV:$DIR/public/aset/"

echo "[3/4] kirim hasil build (file lama dibersihkan)..."
rsync -avz --delete -e "$SSH" public/build/  "$SRV:$DIR/public/build/"

echo "[4/4] bersihkan cache di server..."
$SSH "$SRV" "cd $DIR && php artisan route:clear && php artisan view:clear && php artisan config:clear"

echo
echo "verifikasi:"
printf "  login          : %s\n" "$(curl -s -o /dev/null -w '%{http_code}' "$URL/login")"
printf "  ganti password : %s (302 = benar, dialihkan ke login)\n" "$(curl -s -o /dev/null -w '%{http_code}' "$URL/akun/password")"
CSS=$(curl -s "$URL/login" | grep -oE 'href="[^"]*\.css"' | head -1 | sed 's/href="//;s/"//')
printf "  css terpakai   : %s -> %s\n" "${CSS##*/}" "$(curl -s -o /dev/null -w '%{http_code}' "$CSS")"
echo
echo "selesai. Jika ada migrasi baru, jalankan manual:"
echo "  $SSH $SRV 'cd $DIR && php artisan migrate --force'"
