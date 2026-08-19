@props(['disabled' => false])
<input @disabled($disabled) {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-ui-border bg-ui-card text-ui-text shadow-sm focus:border-pov-pengajuan focus:ring-pov-pengajuan']) }}>
