import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'pov-pengajuan': '#2563EB',
                'pov-verifikasi': '#16A34A',
                'pov-pencairan': '#7C3AED',
                'status-pending': '#F59E0B',
                'status-approved': '#16A34A',
                'status-revisi': '#F97316',
                'status-rejected': '#DC2626',
                'status-done': '#0D9488',
                'status-neutral': '#64748B',
                'ui-page': '#F8FAFC',
                'ui-card': '#FFFFFF',
                'ui-border': '#E2E8F0',
                'ui-text': '#1E293B',
                'ui-muted': '#64748B',
            },
        },
    },

    plugins: [forms],
};
