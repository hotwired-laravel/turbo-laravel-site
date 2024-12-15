@extends('_layouts.main')

@section('body')
  <section class="bg-white/90 custom-border px-6 py-20">
    <div class="space-y-4 sm:space-y-6 sm:max-w-4xl mx-auto w-full text-center">
      <h3 class="text-4xl sm:text-6xl font-heading font-extrabold">Talk is cheap. Try the demo for yourself.</h3>

      <p>A picture is worth a thousand words. Try the demo yourself to picture what a Hotwired app might feel like. Of course, this is a simple demo and Hotwired apps will definitely feel <em>very</em> different from one another. It all depends on how much progressive enhancements you're willing to trade-off.</p>

      <div class="text-left text-lg bg-orange-100/90 shadow-sm text-orange-900/80 border-l-2 border-orange-900/90 p-4">
        The application was deployed using <a href="https://kamal-deploy.org/" class="underline text-orange-900/90">Kamal</a> on my own small server. The database resets every 10 minutes. Please, be respectful when changing data.
      </div>

      <img src="/assets/images/demo.webp" alt="Demo Example" class="rounded shadow" />

      <p>The source code for this sample app is available on <a href="{{ $page->github_demo_app_url }}" class="underline text-zinc-900/90">GitHub</a>.</p>
    </div>

    <hr class="max-w-xs mx-auto my-20">

    <div class="space-y-6 sm:max-w-4xl mx-auto w-full text-center">
      <h3 class="font-heading font-extrabold text-4xl">The Bootcamp</h3>

      <p class="text-center text-lg">In order to help you to get a better understanding of the many sides of Hotwire, we offer a free Bootcamp inspired by the official Laravel Bootcamp. In the Turbo Laravel Bootcamp, you’ll get a hands-on introduction to Hotwire and Turbo Laravel building a web application from scratch and then building the hybrid native app for it.</p>

      <ul class="sm:flex items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
        <li><a href="{{ $page->bootcamp_index }}" class="px-4 py-2 inline-block w-full text-center rounded-full bg-zinc-900/90 text-white transition hover:bg-zinc-900/70">Start learning</a></li>
      </ul>
    </div>
  </section>
@endsection
