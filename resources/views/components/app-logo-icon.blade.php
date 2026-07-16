@props(['variant' => 'mark'])

@if ($variant === 'mark')
    {{-- Icon mark only: three kanban columns of varying card-stack height inside a rounded badge --}}
    <svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'xmlns' => 'http://www.w3.org/2000/svg']) }}>
        <rect x="1" y="1" width="22" height="22" rx="6.5" fill="currentColor" opacity="0.14" />
        <rect x="4.25" y="4.25" width="4.4" height="15.5" rx="1.6" fill="currentColor" />
        <rect x="9.8" y="4.25" width="4.4" height="10" rx="1.6" fill="currentColor" />
        <rect x="15.35" y="4.25" width="4.4" height="12.75" rx="1.6" fill="currentColor" />
    </svg>
@else
    {{-- Full wordmark: mark + "Boardly" text, useful for marketing/email contexts --}}
    <svg {{ $attributes->merge(['viewBox' => '0 0 132 24', 'fill' => 'none', 'xmlns' => 'http://www.w3.org/2000/svg']) }}>
        <rect x="1" y="1" width="22" height="22" rx="6.5" fill="currentColor" opacity="0.14" />
        <rect x="4.25" y="4.25" width="4.4" height="15.5" rx="1.6" fill="currentColor" />
        <rect x="9.8" y="4.25" width="4.4" height="10" rx="1.6" fill="currentColor" />
        <rect x="15.35" y="4.25" width="4.4" height="12.75" rx="1.6" fill="currentColor" />
        <text x="30" y="17" fill="currentColor" font-family="Instrument Sans, ui-sans-serif, system-ui, sans-serif" font-size="16" font-weight="600" letter-spacing="-0.02em">Boardly</text>
    </svg>
@endif
