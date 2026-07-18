@props([
    // list<array{q: string, a: string}> — question/answer pairs, already translated.
    'items'   => [],
    // Section heading. Falls back to the shared "Frequently asked questions" string.
    'title'   => null,
    // Anchor id, so several FAQ blocks can coexist on one page.
    'id'      => 'faq',
    // Emit FAQPage JSON-LD. Set false when the page already ships one.
    'schema'  => true,
])

@php
    // Keep only well-formed pairs so a missing translation never renders an empty row.
    $faqItems = collect($items)
        ->filter(fn ($i) => filled($i['q'] ?? null) && filled($i['a'] ?? null))
        ->values();

    $faqTitle = $title ?? __('app.faq_title');
@endphp

@if($faqItems->isNotEmpty())

    @if($schema)
        @push('schema')
        @php
            // FAQPage schema — answers are plain text (tags stripped) per Google's spec.
            $_faqSchema = json_encode([
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => $faqItems->map(fn ($i) => [
                    '@type'          => 'Question',
                    'name'           => trim(strip_tags($i['q'])),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => trim(strip_tags($i['a'])),
                    ],
                ])->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        @endphp
        <script type="application/ld+json">{!! $_faqSchema !!}</script>
        @endpush
    @endif

    <section class="faq-section" id="{{ $id }}" aria-labelledby="{{ $id }}-title">
        <div class="container">
            <h2 id="{{ $id }}-title" class="faq-title">{{ $faqTitle }}</h2>

            <ul class="faq-list">
                @foreach($faqItems as $i => $item)
                    <li class="faq-item">
                        {{-- <details> gives native keyboard + screen-reader support with no JS. --}}
                        <details class="faq-details" @if($loop->first) open @endif>
                            <summary class="faq-question">
                                <h3 class="faq-question-text">{{ $item['q'] }}</h3>
                                <span class="faq-icon" aria-hidden="true"></span>
                            </summary>
                            <div class="faq-answer">
                                <p>{!! $item['a'] !!}</p>
                            </div>
                        </details>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Styles are emitted inline (once per request) because the app layout uses
         @yield('styles') rather than a stack, so @push would be dropped here. --}}
    @once
    <style>
        .faq-section { padding: 64px 0; background: var(--white, #fff); border-top: 1px solid var(--border, #e5e7eb); }
        .faq-title { font-size: clamp(22px, 3vw, 30px); font-weight: 800; text-align: center; margin: 0 0 32px; color: var(--dark, #111); }
        .faq-list { list-style: none; margin: 0 auto; padding: 0; max-width: 780px; }
        .faq-item + .faq-item { margin-top: 12px; }

        .faq-details { border: 1px solid var(--border, #e5e7eb); border-radius: 12px; background: var(--white, #fff); transition: border-color .2s, box-shadow .2s; }
        .faq-details[open] { border-color: var(--green, #006a3b); box-shadow: 0 2px 16px rgba(0,106,59,.06); }

        .faq-question { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 22px; cursor: pointer; list-style: none; }
        .faq-question::-webkit-details-marker { display: none; }
        .faq-question:hover .faq-question-text { color: var(--green, #006a3b); }
        .faq-question-text { margin: 0; font-size: 15px; font-weight: 700; color: var(--dark, #111); line-height: 1.5; transition: color .2s; }

        /* Chevron drawn in CSS — no icon font, flips with the open state. */
        .faq-icon { flex-shrink: 0; width: 10px; height: 10px; border-right: 2px solid var(--green, #006a3b); border-bottom: 2px solid var(--green, #006a3b); transform: rotate(45deg); transition: transform .2s; margin-top: -4px; }
        .faq-details[open] .faq-icon { transform: rotate(-135deg); margin-top: 2px; }

        .faq-answer { padding: 0 22px 20px; }
        .faq-answer p { margin: 0; font-size: 14px; line-height: 1.8; color: var(--gray, #555); }
        .faq-answer :is(a) { color: var(--green, #006a3b); font-weight: 600; text-decoration: underline; }

        .faq-details :focus-visible { outline: 2px solid var(--green, #006a3b); outline-offset: 2px; border-radius: 12px; }

        @media (max-width: 640px) {
            .faq-section { padding: 44px 0; }
            .faq-question { padding: 15px 16px; }
            .faq-answer { padding: 0 16px 16px; }
        }
    </style>
    @endonce

@endif
