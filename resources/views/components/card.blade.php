@props([
'padding' => 'p-4',
])

<div {{ $attributes->merge([
    'class' => "rounded-xl border border-stone-200 bg-[#fbf8f2] shadow-sm {$padding}",
    ]) }}>
    {{ $slot }}
</div>