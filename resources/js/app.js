import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/*
 * Roda tetikus tidak boleh mengubah isi <input type="number">.
 *
 * Kolom uang sudah memakai komponen x-input-rupiah yang bertipe teks, tapi
 * masih ada kolom angka lain (mis. urutan verifikator). Tanpa penjaga ini,
 * menggulir halaman sementara kursor berada di atas kolom yang sedang fokus
 * akan mengubah nilainya tanpa disadari.
 *
 * Listener dipasang non-passive karena preventDefault() dipakai.
 */
document.addEventListener(
    'wheel',
    (peristiwa) => {
        const elemen = peristiwa.target;

        if (
            elemen instanceof HTMLInputElement &&
            elemen.type === 'number' &&
            elemen === document.activeElement
        ) {
            peristiwa.preventDefault();
        }
    },
    { passive: false },
);
