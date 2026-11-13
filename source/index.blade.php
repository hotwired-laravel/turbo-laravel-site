@extends('_layouts.site')

@php
  $focus = 'focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-focus';
  $inlink = "font-semibold text-wire-strong underline decoration-[1.5px] underline-offset-[3px] hover:text-ink {$focus}";
  $btnPrimary = "inline-flex items-center gap-2 rounded-full bg-ember-strong font-semibold text-white hover:brightness-[.92] {$focus}";
  $btnOutline = "inline-flex items-center rounded-full border-2 border-ink font-semibold text-ink hover:bg-ink hover:text-paper {$focus}";
  $displayH2 = 'font-display font-bold text-[clamp(36px,5vw,52px)] leading-[1.08] tracking-[-0.03em]';
  $tile = "transition duration-150 ease-out hover:-translate-y-0.5 hover:border-ink {$focus}";

  $arrowRight = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>';
  $arrowUpRight = fn ($size) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"></path></svg>';
  $github = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 19c-4 1.3-4-2-6-2.5m12 5v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1-.3-3.4 1.3a11.7 11.7 0 0 0-6.2 0C6.6 3.3 5.6 3.6 5.6 3.6a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4.2 10c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"></path></svg>';
  $copyIcons = '<svg class="group-data-copied:hidden" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8.5" y="8.5" width="11" height="11" rx="2"></rect><path d="M15.5 8.5V6.5a2 2 0 0 0-2-2h-7a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h2"></path></svg><svg class="hidden text-wire group-data-copied:block" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>';

  $features = [
    [
      'title' => 'Decompose screens with Turbo Frames.',
      'body' => 'Turbo Frames let predefined sections of your page update on request, independently of the rest of the page. Build data tables, inline forms and more in the blink of an eye.',
      'href' => '/docs/turbo-frames',
      'snippet' => 'frames',
      'accent' => 'wire',
      'filename' => null,
    ],
    [
      'title' => 'Come alive with Turbo Streams.',
      'body' => 'Turbo Streams deliver page changes as fragments of HTML: synchronously after a form submit, or asynchronously over WebSockets through Laravel Echo. Add a trait to your model and broadcasting just works.',
      'href' => '/docs/turbo-streams',
      'snippet' => 'streams',
      'accent' => 'ember',
      'filename' => null,
    ],
    [
      'title' => 'Go native with iOS & Android.',
      'body' => 'Hotwire Native puts your Laravel application at the center of your iOS and Android apps, with seamless transitions between web and native screens. Turbo Laravel ships helpers for instrumenting your Hotwire Native clients.',
      'href' => '/docs/hotwire-native',
      'snippet' => 'native',
      'accent' => 'wire',
      'filename' => 'app/Http/Controllers/ChirpsController.php',
    ],
    [
      'title' => 'Everything is testable.',
      'body' => 'Turbo Laravel ships testing helpers out of the box, so you can make sure your whole application works. The best part: not much changes from your typical Laravel testing approach.',
      'href' => '/docs/testing',
      'snippet' => 'tests',
      'accent' => 'ember',
      'filename' => 'tests/Feature/CreatesChirpsTest.php',
    ],
  ];

  $skills = [
    ['developing-with-turbo-basics', 'dom_id, turbo_stream() and the x-turbo Blade components.', 'text-syntax-keyword'],
    ['developing-with-turbo-drive', 'SPA-like navigation, morphing and cache control.', 'text-syntax-keyword'],
    ['developing-with-turbo-frames', 'Scoped navigation, frame targeting and lazy loading.', 'text-syntax-keyword'],
    ['developing-with-turbo-streams', 'Partial page updates and real-time broadcasting.', 'text-syntax-keyword'],
    ['developing-with-turbo-tests', 'Testing Turbo and Hotwire Native behavior in Pest or PHPUnit.', 'text-syntax-variable'],
  ];

  $starterKit = [
    ['Turbo', 'Frames, Streams and Hotreload for development.', 'https://turbo.hotwired.dev', 'bg-wire-soft'],
    ['Stimulus', 'Controllers and Hotwire Native Bridge Components.', 'https://stimulus.hotwired.dev', 'bg-paper-sunken'],
    ['Tailwind CSS', 'Via Importmap Laravel and Tailwind CSS Laravel. Vite works too.', 'https://tailwindcss.com', 'bg-ember-soft'],
    ['daisyUI', 'A component library integrated from the start.', 'https://daisyui.com', 'bg-wire-soft'],
  ];

  $bootcamp = [
    ['Installation', '/bootcamp/installation'],
    ['Creating chirps', '/bootcamp/creating-chirps'],
    ['Listing chirps', '/bootcamp/listing-chirps'],
    ['Editing chirps', '/bootcamp/editing-chirps'],
    ['Deleting chirps', '/bootcamp/deleting-chirps'],
    ['Hotwiring everything', '/bootcamp/hotwiring-everything'],
    ['Broadcasting', '/bootcamp/broadcasting'],
    ['Native apps', '/bootcamp/native-setup'],
  ];
@endphp

@section('body')
<main>
  {{-- Hero --}}
  <section class="mx-auto flex max-w-[1248px] flex-col items-center gap-7 px-6 pt-24 pb-[88px] text-center">
    <h1 class="max-w-[15ch] font-display text-[clamp(44px,7vw,80px)] leading-none font-extrabold tracking-[-0.035em]">
      Build web and hybrid native apps in <span class="text-ember">Laravel</span> <em class="text-wire-strong">today</em>.
    </h1>

    <p class="max-w-[36em] text-xl leading-8 text-ink-muted">
      Turbo Laravel integrates your Laravel application with <a class="{{ $inlink }}" href="{{ $page->hotwire_site_url }}">Hotwire</a>, an alternative way of building web and native apps with minimal JavaScript, by sending HTML over the wire.
    </p>

    <div class="flex flex-wrap justify-center gap-3">
      <a href="{{ $page->current_docs_index }}" class="{{ $btnPrimary }} min-h-12 px-6 py-3 text-base">Read the docs {!! $arrowRight !!}</a>
      <a href="{{ $page->github_url }}" class="{{ $btnOutline }} min-h-12 px-6 py-3 text-base">View the source</a>
    </div>

    <div data-controller="clipboard" class="group mt-2 flex max-w-full items-center gap-3.5 rounded-full bg-code-bg py-2.5 pr-2.5 pl-5 font-code text-sm leading-5 text-code-ink">
      <span class="text-wire" aria-hidden="true">$</span>
      <span data-clipboard-target="source" class="overflow-x-auto whitespace-nowrap">composer require hotwired-laravel/turbo-laravel</span>
      <button type="button" data-action="clipboard#copy" aria-label="Copy install command" class="inline-flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-full bg-code-chrome text-code-muted hover:text-code-ink focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-syntax-keyword">{!! $copyIcons !!}</button>
    </div>
  </section>

  {{-- Your typical Laravel app --}}
  <section class="border-y border-line bg-paper-sunken">
    <div class="mx-auto max-w-[1248px] px-6 pt-[88px] pb-16">
      <div class="flex max-w-[760px] flex-col gap-4">
        <h2 class="{{ $displayH2 }}">Your typical Laravel app.</h2>
        <p class="text-lg leading-[30px] text-ink-muted">Hotwire was built for full-stack web frameworks like Laravel and Rails. Modern web applications don't have to be built as stateful components. Keep writing regular <a class="{{ $inlink }}" href="https://youtu.be/MF0jFKvS4SI?si=1wGv8BYVd8v8IdA2">Cruddy applications</a>, with controllers and Blade views, and progressively enhance the <em>fidelity</em> of your UI with Turbo Frames, Turbo Streams, or a sprinkle of JavaScript.</p>
      </div>
    </div>

    <div class="border-t border-line bg-paper-raised">
      <ol class="mx-auto grid max-w-[1248px] grid-cols-[repeat(auto-fit,minmax(260px,1fr))] gap-px bg-line">
        <li class="flex flex-col gap-2 bg-paper-raised px-6 pt-9 pb-10">
          <div class="ill mb-5 overflow-hidden rounded-[14px] border border-line bg-paper" aria-hidden="true">
            <svg viewBox="0 0 320 184" width="100%" class="block" font-family="'JetBrains Mono', monospace">
              <rect x="17" y="17" width="296" height="156" rx="10" fill="#5cd8e5"></rect>
              <rect x="12" y="12" width="296" height="156" rx="10" fill="#ffffff" stroke="#0f1a2a" stroke-width="1.5"></rect>
              <text x="24" y="30" font-size="9.5" fill="#4b5768">ChirpController.php</text>
              <path d="M12.75 40H307.25" stroke="#e4ddd0" stroke-width="1"></path>
              <rect x="18" y="107" width="284" height="19" rx="4" fill="#dcf5f7"></rect>
              <g font-size="10.5" fill="#0f1a2a">
                <text x="24" y="60"><tspan fill="#0a6b78">public function </tspan>store(<tspan fill="#857b6a">Request </tspan>$request)</text>
                <text x="24" y="80">{</text>
                <text x="24" y="100">    Chirp::create($request-&gt;validated());</text>
                <text x="24" y="120">    <tspan fill="#c42416" font-weight="500">return </tspan>to_route(<tspan fill="#a8431a">'chirps.index'</tspan>);</text>
                <text x="24" y="140">}</text>
              </g>
            </svg>
          </div>
          <h3 class="font-display text-[26px] leading-8 font-bold tracking-[-0.02em]">Write a regular controller</h3>
          <p class="text-[15px] leading-6 text-ink-muted">Routes, form requests, Eloquent, redirects. Nothing new to learn on the server.</p>
        </li>

        <li class="flex flex-col gap-2 bg-paper-raised px-6 pt-9 pb-10">
          <div class="ill mb-5 overflow-hidden rounded-[14px] border border-line bg-paper" aria-hidden="true">
            <svg viewBox="0 0 320 184" width="100%" class="block" font-family="'JetBrains Mono', monospace">
              <rect x="17" y="17" width="296" height="156" rx="10" fill="#5cd8e5"></rect>
              <rect x="12" y="12" width="296" height="156" rx="10" fill="#ffffff" stroke="#0f1a2a" stroke-width="1.5"></rect>
              <text x="24" y="30" font-size="9.5" fill="#4b5768">chirps/index.blade.php</text>
              <path d="M12.75 40H307.25" stroke="#e4ddd0" stroke-width="1"></path>
              <rect x="28" y="50" width="96" height="9" rx="3" fill="#0f1a2a"></rect>
              <rect x="28" y="65" width="164" height="6" rx="3" fill="#e4ddd0"></rect>
              <rect x="24" y="84" width="272" height="72" rx="8" fill="#effafb" stroke="#0a6b78" stroke-width="1.5" stroke-dasharray="5 4"></rect>
              <rect x="36" y="77" width="104" height="15" rx="7.5" fill="#0a6b78"></rect>
              <text x="44" y="87.5" font-size="9" fill="#ffffff">&lt;x-turbo::frame&gt;</text>
              <circle cx="44" cy="108" r="7" fill="#f3eee4" stroke="#857b6a" stroke-width="1.25"></circle>
              <rect x="58" y="102" width="118" height="5" rx="2.5" fill="#4b5768"></rect>
              <rect x="58" y="111" width="184" height="5" rx="2.5" fill="#d3e7ea"></rect>
              <circle cx="44" cy="136" r="7" fill="#f3eee4" stroke="#857b6a" stroke-width="1.25"></circle>
              <rect x="58" y="130" width="96" height="5" rx="2.5" fill="#4b5768"></rect>
              <rect x="58" y="139" width="168" height="5" rx="2.5" fill="#d3e7ea"></rect>
            </svg>
          </div>
          <h3 class="font-display text-[26px] leading-8 font-bold tracking-[-0.02em]">Return Blade HTML</h3>
          <p class="text-[15px] leading-6 text-ink-muted">Render the views you already have. Wrap the parts that change in a frame or a stream.</p>
        </li>

        <li class="flex flex-col gap-2 bg-paper-raised px-6 pt-9 pb-10">
          <div class="ill mb-5 overflow-hidden rounded-[14px] border border-line bg-paper" aria-hidden="true">
            <svg viewBox="0 0 320 184" width="100%" class="block" font-family="'JetBrains Mono', monospace">
              <rect x="17" y="17" width="296" height="156" rx="10" fill="#5cd8e5"></rect>
              <rect x="12" y="12" width="296" height="156" rx="10" fill="#ffffff" stroke="#0f1a2a" stroke-width="1.5"></rect>
              <rect x="22" y="19" width="160" height="15" rx="7.5" fill="#f3eee4"></rect>
              <text x="31" y="29.5" font-size="9" fill="#4b5768">/chirps</text>
              <path d="M12.75 40H307.25" stroke="#e4ddd0" stroke-width="1"></path>
              <rect x="28" y="50" width="96" height="9" rx="3" fill="#0f1a2a"></rect>
              <rect x="28" y="65" width="164" height="6" rx="3" fill="#e4ddd0"></rect>
              <rect x="24" y="84" width="272" height="72" rx="8" fill="#ffffff" stroke="#0a6b78" stroke-width="1.5"></rect>
              <g class="tl-old">
                <circle cx="44" cy="136" r="7" fill="#f3eee4" stroke="#857b6a" stroke-width="1.25"></circle>
                <rect x="58" y="130" width="118" height="5" rx="2.5" fill="#4b5768"></rect>
                <rect x="58" y="139" width="184" height="5" rx="2.5" fill="#e4ddd0"></rect>
              </g>
              <g class="tl-new">
                <rect x="30" y="92" width="260" height="32" rx="8" fill="#effafb" stroke="#0a6b78" stroke-width="1.5" stroke-dasharray="5 4"></rect>
                <circle cx="44" cy="108" r="7" fill="#5cd8e5" stroke="#0a6b78" stroke-width="1.25"></circle>
                <rect x="58" y="102" width="104" height="5" rx="2.5" fill="#0a6b78"></rect>
                <rect x="58" y="111" width="150" height="5" rx="2.5" fill="#9fd9df"></rect>
                <rect x="42" y="84.5" width="110" height="15" rx="7.5" fill="#0a6b78"></rect>
                <text x="50" y="95" font-size="9" fill="#ffffff">&lt;x-turbo::stream&gt;</text>
              </g>
            </svg>
          </div>
          <h3 class="font-display text-[26px] leading-8 font-bold tracking-[-0.02em]">Turbo updates the page</h3>
          <p class="text-[15px] leading-6 text-ink-muted">No full reloads, no JSON APIs, no client-side state to keep in sync.</p>
        </li>
      </ol>
    </div>
  </section>

  {{-- Features --}}
  <div class="mx-auto flex max-w-[1248px] flex-col gap-8 px-6 py-12">
    @foreach ($features as $feature)
      <section @class([
        'flex flex-wrap items-center gap-x-16 gap-y-10 py-14',
        'flex-row-reverse' => $loop->odd === false,
      ])>
        <div class="flex min-w-0 flex-[1_1_340px] flex-col items-start gap-4">
          <h2 class="font-display text-[40px] leading-[44px] font-bold tracking-[-0.03em]">{{ $feature['title'] }}</h2>
          <p class="text-[17px] leading-7 text-ink-muted">{{ $feature['body'] }}</p>
          <a href="{{ $feature['href'] }}" class="{{ $btnOutline }} min-h-11 px-[18px] py-2 text-[15px]">Read more</a>
        </div>

        <div class="min-w-0 flex-[1.4_1_480px] pr-2 pb-2">
          <x-code-panel :accent="$feature['accent']" :filename="$feature['filename']">
            {!! $page->snippets[$feature['snippet']] !!}
          </x-code-panel>
        </div>
      </section>
    @endforeach
  </div>

  {{-- Agent ready --}}
  <section class="mx-auto max-w-[1248px] px-6 pt-12 pb-28">
    <div class="flex flex-col gap-14">
      <div class="flex max-w-[900px] flex-col gap-5">
        <h2 class="font-display text-[clamp(36px,5.2vw,60px)] leading-[1.04] font-extrabold tracking-[-0.035em]">Just because your agent can generate thousands of lines of code doesn't mean it <span class="text-ember">should</span>.</h2>
        <p class="max-w-[40em] text-xl leading-8 text-ink-muted">With Hotwire, humans and agents build more with less. Plain controllers, Blade views and a sprinkle of Stimulus mean less code to generate, less code to review, and fewer places for bugs to hide.</p>
      </div>

      <div class="flex flex-wrap items-start gap-x-16 gap-y-10">
        <div class="flex min-w-0 flex-[1_1_320px] flex-col gap-6">
          <div class="flex flex-col gap-1.5">
            <h3 class="font-display text-[21px] leading-7 font-[650]">Skills your agent can load</h3>
            <p class="text-base leading-[26px] text-ink-muted">Turbo Laravel ships <a class="{{ $inlink }}" href="https://github.com/laravel/boost">Laravel Boost</a> guidelines and skills, so your agent writes idiomatic Turbo code from the first prompt.</p>
          </div>
          <div class="flex flex-col gap-1.5">
            <h3 class="font-display text-[21px] leading-7 font-[650]">Stimulus and Native, too</h3>
            <p class="text-base leading-[26px] text-ink-muted">Stimulus Laravel publishes its own Boost guidelines, including one for Hotwire Native Bridge Components.</p>
          </div>
          <div class="flex flex-col gap-1.5">
            <h3 class="font-display text-[21px] leading-7 font-[650]">Small diffs, easy reviews</h3>
            <p class="text-base leading-[26px] text-ink-muted">A frame here, a stream there. Changes stay in the files you already know, so reviewing your agent's work takes minutes.</p>
          </div>
        </div>

        <div class="min-w-0 flex-[1.3_1_460px] pr-2 pb-2">
          <x-code-panel filename="resources/boost/skills">
            <ul class="py-3 font-code">
              @foreach ($skills as [$name, $description, $color])
                <li @class(['flex flex-col gap-0.5 px-6 py-3', 'border-t border-code-chrome' => ! $loop->first])>
                  <span class="text-sm leading-[22px] {{ $color }}">{{ $name }}</span>
                  <span class="font-body text-sm leading-[22px] text-code-muted">{{ $description }}</span>
                </li>
              @endforeach
            </ul>
          </x-code-panel>
        </div>
      </div>
    </div>
  </section>

  {{-- Starter kit --}}
  <section class="mx-auto max-w-[1248px] px-6 pb-24">
    <div class="flex flex-wrap items-center gap-x-16 gap-y-10 rounded-2xl border border-line bg-paper-raised p-[clamp(28px,5vw,56px)]">
      <div class="flex min-w-0 flex-[1_1_380px] flex-col items-start gap-4">
        <h2 class="font-display text-[40px] leading-[44px] font-bold tracking-[-0.03em]">Start a new app, already Hotwired.</h2>
        <p class="text-[17px] leading-7 text-ink-muted">A community-built starter kit with authentication, Turbo Laravel, Stimulus, Tailwind CSS and daisyUI components wired together. A #nobuild frontend out of the box, ready for Hotwire Native and for your coding agent. No Node.js required.</p>

        <div data-controller="clipboard" class="group flex w-full items-start gap-3 rounded-[10px] bg-code-bg py-3 pr-3 pl-[18px] font-code text-sm leading-[22px] text-code-ink">
          <span class="pt-[7px] text-syntax-variable" aria-hidden="true">$</span>
          <code data-clipboard-target="source" class="min-w-0 flex-1 pt-[7px] wrap-anywhere whitespace-pre-wrap">laravel new my-app --pest --no-node \
    --using=hotwired-laravel/hotwire-starter-kit</code>
          <button type="button" data-action="clipboard#copy" aria-label="Copy install command" class="inline-flex size-9 flex-none cursor-pointer items-center justify-center rounded-lg bg-code-chrome text-code-muted hover:text-code-ink focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-syntax-keyword">{!! $copyIcons !!}</button>
        </div>

        <a href="https://github.com/hotwired-laravel/hotwire-starter-kit" class="{{ $btnPrimary }} mt-1 min-h-11 px-5 py-2.5 text-[15px]">Explore the starter kit {!! $arrowRight !!}</a>
      </div>

      <ul class="grid min-w-0 flex-[1_1_340px] grid-cols-2 gap-3">
        @foreach ($starterKit as [$title, $description, $href, $bg])
          <li class="flex">
            <a href="{{ $href }}" class="{{ $tile }} {{ $bg }} relative flex flex-1 flex-col gap-1 rounded-[10px] border border-transparent py-5 pr-10 pl-5 text-ink">
              <span class="absolute top-4 right-3.5 flex text-ink-muted">{!! $arrowUpRight(18) !!}</span>
              <span class="font-display text-[19px] leading-6 font-bold">{{ $title }}</span>
              <span class="text-sm leading-[22px] text-ink-muted">{{ $description }}</span>
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  {{-- Bootcamp --}}
  <section class="bg-ink text-night-ink">
    <div class="mx-auto flex max-w-[1248px] flex-wrap items-start gap-x-[72px] gap-y-12 px-6 py-24">
      <div class="flex min-w-0 flex-[1_1_360px] flex-col items-start gap-[18px]">
        <h2 class="{{ $displayH2 }}">The Bootcamp.</h2>
        <p class="text-lg leading-[30px] text-night-muted">Inspired by the official Laravel Bootcamp: a hands-on introduction to Hotwire and Turbo Laravel. You'll build a web application from scratch, then the hybrid native app for it.</p>
        <a href="{{ $page->bootcamp_index }}" class="mt-1.5 inline-flex min-h-12 items-center gap-2 rounded-full bg-wire px-6 py-3 text-base font-semibold text-ink hover:brightness-[.94] focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-syntax-keyword">Start learning {!! $arrowRight !!}</a>
      </div>

      <ol class="grid min-w-0 flex-[1.2_1_420px] grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-3">
        @foreach ($bootcamp as [$title, $href])
          <li class="flex">
            <a href="{{ $href }}" class="flex flex-1 items-baseline gap-3.5 rounded-[10px] bg-code-chrome px-[18px] py-4 text-night-ink ring-wire/0 transition hover:ring-1 hover:ring-wire focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-syntax-keyword">
              <span @class(['font-code text-[13px] leading-4 font-medium', 'text-syntax-keyword' => $loop->index < 6, 'text-[#ff6a5c]' => $loop->index >= 6])>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
              <span class="text-base leading-6 font-semibold">{{ $title }}</span>
            </a>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  {{-- Pairs well with --}}
  <section class="mx-auto flex max-w-[1248px] flex-col gap-10 px-6 py-28">
    <div class="flex flex-col gap-4">
      <h2 class="{{ $displayH2 }}">Pairs well with.</h2>
      <p class="max-w-[36em] text-lg leading-[30px] text-ink-muted">Turbo Laravel is one piece of the Hotwired Laravel stack. Add the rest as you need it.</p>
    </div>

    <div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4">
      @foreach ([
        ['Stimulus Laravel', 'The modest JavaScript framework for the HTML you already have.', 'https://github.com/hotwired-laravel/stimulus-laravel', 'stimulus', 'bg-[#fff3d1]'],
        ['Importmap Laravel', 'JavaScript modules without a bundler or build step.', 'https://github.com/tonysm/importmap-laravel', 'importmap', 'bg-wire-soft'],
        ['Tailwind CSS Laravel', 'The standalone Tailwind CLI, wired into Laravel.', 'https://github.com/tonysm/tailwindcss-laravel', 'tailwind', 'bg-[#e3f2fb]'],
        ['Hotreload', 'Hot reloading for Turbo Laravel apps during development.', 'https://github.com/hotwired-laravel/hotreload', 'hotreload', 'bg-ember-soft'],
      ] as [$title, $description, $href, $illustration, $bg])
        <a href="{{ $href }}" class="{{ $tile }} relative flex flex-col justify-between gap-5 rounded-2xl border border-line bg-paper-raised px-4 pt-4 pb-5 text-ink">
          <span class="absolute top-5 right-[18px] flex text-ink-subtle">{!! $arrowUpRight(22) !!}</span>
          <span class="inline-flex items-center gap-2 self-start rounded-full bg-ink px-3.5 py-1.5 text-[13px] leading-[18px] font-semibold text-white">{!! $github !!}Source</span>
          <span aria-hidden="true" class="{{ $bg }} block overflow-hidden rounded-[10px]">
            @include('_layouts.partials.home.illustrations.' . $illustration)
          </span>
          <span class="flex flex-col gap-2.5">
            <span class="font-display text-[28px] leading-8 font-bold tracking-[-0.025em]">{{ $title }}</span>
            <span class="text-[15px] leading-6 text-ink-muted">{{ $description }}</span>
          </span>
        </a>
      @endforeach
    </div>
  </section>
</main>
@endsection
