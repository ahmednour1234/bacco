@props(['color' => '#006A3B'])

<svg {{ $attributes->merge(['class' => '']) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 230 58" fill="none" role="img" aria-label="Qimta">
    {{-- "Qimta" text --}}
    <text x="2" y="46"
          font-family="Cairo, sans-serif"
          font-size="46"
          font-weight="800"
          fill="{{ $color }}"
          letter-spacing="-0.5">Qimta</text>

    {{-- Rounded square outline --}}
    <rect x="165" y="4" width="52" height="52" rx="11"
          stroke="{{ $color }}" stroke-width="3.2" fill="none"/>

    {{-- Hashtag vertical lines (slightly angled right, like the logo image) --}}
    <line x1="178" y1="13" x2="174" y2="43"
          stroke="{{ $color }}" stroke-width="3" stroke-linecap="round"/>
    <line x1="192" y1="13" x2="188" y2="43"
          stroke="{{ $color }}" stroke-width="3" stroke-linecap="round"/>

    {{-- Hashtag horizontal lines --}}
    <line x1="172" y1="25" x2="202" y2="25"
          stroke="{{ $color }}" stroke-width="3" stroke-linecap="round"/>
    <line x1="170" y1="37" x2="200" y2="37"
          stroke="{{ $color }}" stroke-width="3" stroke-linecap="round"/>
</svg>
