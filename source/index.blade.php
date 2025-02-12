@extends('_layouts.main')

@section('body')
  <main>
    <div class="sm:max-w-5xl mx-auto w-full px-6 py-12 space-y-4 sm:space-y-12  xl:space-y-6">
      <h2 class="font-heading text-4xl sm:text-7xl font-extrabold text-center">
          Build web and hybrid native apps in Laravel <em>today</em>.
      </h2>

      <p class="text-center font-sans text-xl sm:text-2xl text-zinc-900/80">
        Turbo Laravel integrates your Laravel application with <a href="{{ $page->hotwire_site_url }}" class="underline text-zinc-900/90">Hotwire</a>, an alternative way of building web and native apps with minimal JavaScript, sending HTML over the wire.
      </p>

      <ul class="sm:flex items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
        <li><a href="/docs" class="px-4 py-2 inline-block w-full text-center rounded-full bg-zinc-900/90 text-white transition hover:bg-zinc-900/70">Read the docs</a></li>
        <li><a href="{{ $page->github_url }}" class="px-4 py-2 inline-block w-full text-center rounded-full bg-zinc-900/90 text-white transition hover:bg-zinc-900/70">View the source</a></li>
        <!-- <li><a href="/demo" class="px-4 py-2 inline-block w-full text-center rounded-full bg-zinc-900/90 text-white transition hover:bg-zinc-900/70">Try the demo</a></li> -->
      </ul>
    </div>
  </main>

  <section class="bg-white/90 custom-border px-6 py-20">
    <div class="space-y-4 sm:space-y-6 sm:max-w-5xl mx-auto w-full text-center">
      <h3 class="text-4xl sm:text-6xl font-heading font-extrabold">Your typical Laravel app.</h3>
      <p class="text-xl">Hotwire was built for full-stack web frameworks, like Laravel and Rails. Modern web applications don't have to be built as stateful components. We can keep writing regular <a class="underline text-zinc-900/90" href="https://youtu.be/MF0jFKvS4SI?si=1wGv8BYVd8v8IdA2">Cruddy applications</a>, with controllers, Blade views and so on, and progressively enhance the <em>fidelity</em> of our UIs using Turbo Frames, Turbo Streams, or JavaScript.</p>
    </div>

    <div class="mt-20 lg:max-w-5xl mx-auto w-full space-y-20 lg:space-y-0 lg:grid grid-cols-2 gap-20 thin-scrollbar">
      <div class="space-y-4">
        <div class="overflow-x-auto text-sm rounded">
          {!! $page->snippets['frames'] !!}
        </div>

        <p class="text-2xl font-heading font-extrabold text-center text-zinc-900/90">Decompose screens with Turbo Frames.</p>

        <p class="text-center text-lg">Turbo Frames allow predefined sections of your page to be updated on request, independently of the rest of the page. We can build things like data tables, inline forms, and so on with a blink of an eye.</p>

        <div class="flex items-center justify-center">
            <a href="/docs/turbo-frames" class="px-4 py-2 inline-block w-full mx-auto sm:w-auto text-center rounded-full border border-zinc-900/30 hover:bg-zinc-900/5 text-zinc-900 transition">Read more</a>
        </div>
      </div>

      <div class="space-y-4">
        <div class="overflow-x-auto text-sm rounded">
          {!! $page->snippets['streams'] !!}
        </div>

        <p class="text-2xl font-heading font-extrabold text-center text-zinc-900/90">Come Alive with Turbo Streams.</p>

        <p class="text-center text-lg">Turbo Streams deliver page changes as fragments of HTML. They may be delivered to the browser synchronously after form submits, or asynchronously over WebSockets via Laravel Echo.</p>

        <div class="flex items-center justify-center">
            <a href="/docs/turbo-streams" class="px-4 py-2 inline-block w-full mx-auto sm:w-auto text-center rounded-full border border-zinc-900/30 hover:bg-zinc-900/5 text-zinc-900 transition">Read more</a>
        </div>
      </div>

      <div class="space-y-4">
        <div class="overflow-x-auto text-sm rounded">
          {!! $page->snippets['native'] !!}
        </div>

        <p class="text-2xl font-heading font-extrabold text-center text-zinc-900/90">Go Native with iOS & Android.</p>

        <p class="text-center text-lg">Hotwire Native lets your Laravel application be the center of your native iOS and Android apps, with seamless transitions between web and native screens. Turbo Laravel has tons of helpers for instrumenting your Hotwire Native clients!</p>

        <div class="flex items-center justify-center">
            <a href="/docs/hotwire-native" class="px-4 py-2 inline-block w-full mx-auto sm:w-auto text-center rounded-full border border-zinc-900/30 hover:bg-zinc-900/5 text-zinc-900 transition">Read more</a>
        </div>
      </div>

      <div class="space-y-4">
        <div class="overflow-x-auto text-sm rounded">
          {!! $page->snippets['tests'] !!}
        </div>

        <p class="text-2xl font-heading font-extrabold text-center text-zinc-900/90">Everything is testable.</p>

        <p class="text-center text-lg">Turbo Laravel ships with a series of testing helpers out of the box, so you can ensure your application is fully tested. The best part is that not much changes from your typical Laravel testing approach.</p>

        <div class="flex items-center justify-center">
            <a href="/docs/testing" class="px-4 py-2 inline-block w-full mx-auto sm:w-auto text-center rounded-full border border-zinc-900/30 hover:bg-zinc-900/5 text-zinc-900 transition">Read more</a>
        </div>
      </div>
    </div>

    <hr class="max-w-xs mx-auto my-20">

    <div class="space-y-6 sm:max-w-5xl mx-auto w-full text-center">
      <h3 class="font-heading font-extrabold text-4xl">The Bootcamp</h3>

      <p class="text-center text-lg">In order to help you to get a better understanding of the many sides of Hotwire, we offer a free Bootcamp inspired by the official Laravel Bootcamp. In the Turbo Laravel Bootcamp, you’ll get a hands-on introduction to Hotwire and Turbo Laravel building a web application from scratch and then building the hybrid native app for it.</p>

      <ul class="sm:flex items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
        <li><a href="{{ $page->bootcamp_index }}" class="px-4 py-2 inline-block w-full text-center rounded-full bg-zinc-900/90 text-white transition hover:bg-zinc-900/70">Start learning</a></li>
      </ul>
    </div>
  </section>
@endsection
