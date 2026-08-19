<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-lg bg-pov-pengajuan px-4 py-2 text-sm font-semibold text-ui-card shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-pov-pengajuan focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
