import './bootstrap';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import dataTable from './data-table';

window.Alpine = Alpine;
window.Swal = Swal;

// Register reusable Alpine components
Alpine.data('dataTable', dataTable);

// SIHRS notification helpers — konsisten dengan brand color teal + Bahasa Indonesia
window.notify = {
    success(text, title = 'Berhasil') {
        return Swal.fire({
            icon: 'success',
            title,
            text,
            confirmButtonColor: '#0d9488',
            timer: 3000,
            timerProgressBar: true,
        });
    },
    error(text, title = 'Terjadi Kesalahan') {
        return Swal.fire({
            icon: 'error',
            title,
            text,
            confirmButtonColor: '#dc2626',
        });
    },
    warning(text, title = 'Perhatian') {
        return Swal.fire({
            icon: 'warning',
            title,
            text,
            confirmButtonColor: '#d97706',
        });
    },
    info(text, title = 'Informasi') {
        return Swal.fire({
            icon: 'info',
            title,
            text,
            confirmButtonColor: '#0d9488',
        });
    },
    confirm(text, title = 'Konfirmasi') {
        return Swal.fire({
            icon: 'question',
            title,
            text,
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#6b7280',
        });
    },
};

Alpine.start();
