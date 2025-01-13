# *06.* Hotwiring everything

[TOC]

## Introduction

So far, our application is quite basic. Out of Hotwire, we're only using Turbo Drive, which is enabled by default when we install and start Turbo.

## Using Turbo Frames to render the create Chirps form inline

Our application works, but we could improve it. Instead of sending users to a dedicated chirp creation form page, let's display the form inline right on the `chirps.index` page. To do that, we're going to use [lazy-loading Turbo Frames](https://turbo.hotwired.dev/reference/frames):

```blade filename="resources/views/chirps/index.blade.php"
<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[__('Chirps')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    @include('chirps.partials.new-chirp-trigger')
                    <x-turbo::frame id="create_chirp" src="{{ route('chirps.create') }}">
                        @include('chirps.partials.new-chirp-trigger')
                    </x-turbo::frame><!-- [tl! remove:-3,1 add:-2,3] -->

                    <div class="mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:divide-gray-500">
                        @each('chirps.partials.chirp', $chirps, 'chirp')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

For that to work, we also need to wrap our create form with a matching Turbo Frame (by "matching" I mean same DOM ID):

```blade filename=resources/views/chirps/create.blade.php
<x-app-layout :title="__('Create Chirp')">
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[route('chirps.index') => __('Chirps'), __('New Chirp')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    @include('chirps.partials.form')
                    <x-turbo::frame id="create_chirp" target="_top">
                        @include('chirps.partials.form')
                    </x-turbo::frame><!-- [tl! remove:-3,1 add:-2,3]-->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

A few things about this:

1. In the `chirps.index`, we specified the Turbo Frame with the `src` attribute, which indicates Turbo that this is lazy-loading Turbo Frame
1. The Turbo Frame in the `chirps.create` page has a `target="_top"` on it. That's not gonna be used, it's just in case someone opens that page directly by visiting `/chirps/create` or disables JavaScript (in this case, they would still see the link pointing to the create chirps page, so they would be able to use our application normally)

If you try to use the form now, you will see a strange behavior where the form disappears after you submit it and the link is back. If you refresh the page, you'll see the chirp was successfully created.

That happens because we're redirecting users to the `chirps.index` page after the form submission. That page has a matching Turbo Frame, which contains the link. Nothing else on the page changes because of the Turbo Frame contains the page changes to only its fragment.

Let's make use of Turbo Streams to update our form with a clean one and prepend the recently created Chirp to the chirps list.

### Reseting the form and prepeding Chirps to the list

Before we change the `ChirpController`, let's give our list of chirps wrapper element an ID in the `chirps.index` page:

```blade filename=resources/views/chirps/index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[__('Chirps')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    <x-turbo::frame id="create_chirp" src="{{ route('chirps.create') }}">
                        @include('chirps.partials.new-chirp-trigger')
                    </x-turbo::frame>

                    <div class="mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:divide-gray-500">
                    <div id="chirps" class="mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:divide-gray-500"> <!-- [tl! remove:-1,1 add]-->
                        @each('chirps.partials.chirp', $chirps, 'chirp')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

Okay, now we can change the `store` action in our `ChirpController` to return 3 Turbo Streams if the client supports it, one to update the form with a clean one, another to prepend the new chirp to the list, and another to append the flash message:

```php filename=app/Http/Controllers/ChirpController.php
<?php
// [tl! collapse:start]
namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
// [tl! collapse:end]
class ChirpController extends Controller
{
    // [tl! collapse:start]
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('chirps.index', [
            'chirps' => Chirp::with('user:id,name')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('chirps.create');
    }
    // [tl! collapse:end]
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->chirps()->create($validated);// [tl! remove]
        $chirp = $request->user()->chirps()->create($validated);// [tl! add:start]

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps.partials.form')),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp created.')])),
            ]);
        }// [tl! add:end]

        return redirect()
            ->route('chirps.index')
            ->with('status', __('Chirp created.'));
    }
    // [tl! collapse:start]
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function edit(Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp->update($validated);

        return redirect()
            ->route('chirps.index')
            ->with('status', __('Chirp updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chirp         $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        return redirect()
            ->route('chirps.index')
            ->with('status', __('Chirp deleted.'));
    }
    // [tl! collapse:end]
}
```

If you try to create one now, you'll notice Turbo Laravel expects to find the Chirp partial at `resources/views/chirps/_chirp.blade.php`, but we are using a folder-based partial convention. This pattern is common in Laravel, so Turbo Laravel understands that as well. Let's update the package to use that. Update your `AppServiceProvider` like so:

```php
<?php

namespace App\Providers;

use HotwiredLaravel\TurboLaravel\Facades\Turbo; // [tl! add]
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // [tl! collapse:start]
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
    // [tl! collapse:end]
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Turbo::usePartialsSubfolderPattern(); // [tl! remove:-1,1 add]
    }
}
```

Now if you try creating a Chirp, you should see the newly created Chirp at the top of the chirps list, the form should have been cleared, and a flash message showed up.

![Hotwiring Chirps Creationg](/images/bootcamp/hotwiring-creating-chirps.png)

Let's also implement inline editing for our chirps.

## Displaying the edit chirps form inline

To do that, we need to tweak our `chirps.partials.chirp` partial and wrap it with a Turbo Frame. Instead of showing you a long Git diff, replace the existing partial with this one:

```blade filename=resources/views/chirps/partials/chirp.blade.php
<div class="p-6 flex space-x-2">
<x-turbo::frame :id="$chirp" class="p-6 flex space-x-2"> <!-- [tl! remove:-1,1 add] -->
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-400 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <!-- [tl! collapse:start] -->
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        <!-- [tl! collapse:end] -->
    </svg>

    <div class="flex-1">
        <!-- [tl! collapse:start] -->
        <div class="flex justify-between items-center">
            <div>
                <span class="text-gray-800 dark:text-gray-200">{{ $chirp->user->name }}</span>
                <small class="ml-2 text-sm text-gray-600 dark:text-gray-400"><x-relative-time :date="$chirp->created_at" /></small>
                @unless ($chirp->created_at->eq($chirp->updated_at))
                <small class="text-sm text-gray-600"> &middot; edited</small>
                @endunless
            </div>
            @if (Auth::id() === $chirp->user->id)
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link href="{{ route('chirps.edit', $chirp) }}">{{ __('Edit') }}</x-dropdown-link>

                    <form action="{{ route('chirps.destroy', $chirp) }}" method="POST">
                        @method('DELETE')

                        <x-dropdown-button type="submit">{{ __('Delete') }}</x-dropdown-button>
                    </form>
                </x-slot>
            </x-dropdown>
            @endif
        </div>
        <p class="mt-4 text-lg text-gray-900 dark:text-gray-200">{{ $chirp->message }}</p>
        <!-- [tl! collapse:end] -->
    </div>
</div>
</x-turbo::frame> <!-- [tl! remove:-1,1 add]-->
```

Now, let's also update the `chirps.edit` page to add a wrapping Turbo Frame around the form there:

```blade filename=resources/views/chirps/edit.blade.php
<x-app-layout :title="__('Edit Chirp')">
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[route('chirps.index') => __('Chirps'), __('Edit Chirp')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    @include('chirps.partials.form', ['chirp' => $chirp])
                    <x-turbo::frame :id="$chirp" target="_top">
                        @include('chirps.partials.form', ['chirp' => $chirp])
                    </x-turbo::frame><!-- [tl! remove:-3,1 add:-2,3] -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

## Updating inline edit form with the chirps partial

Now, if you try clicking on the edit button, you should see the form appearing inline! If you submit it, you will see the change takes place already. That's awesome, right? Well, not so much. See, we can see the change because after the chirp is updated, the controller redirects the user to the index page and it happens that the chirp is rendered on that page, so it finds a matching Turbo Frame. If that wasn't the case, we would see a strange behavior.

Let's change the `update` action in the `ChirpController` to return a Turbo Stream with the updated Chirp partial if the client supports it:

```php filename=app/Controllers/ChirpController.php
<?php
// [tl! collapse:start]
namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
// [tl! collapse:end]
class ChirpController extends Controller
{
    // [tl! collapse:start]
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('chirps.index', [
            'chirps' => Chirp::with('user')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('chirps.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp = $request->user()->chirps()->create($validated);

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps.partials.form')),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp created.')])),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp created.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function edit(Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }
    // [tl! collapse:end]
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp->update($validated);

        if ($request->wantsTurboStream()) {// [tl! add:start]
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp updated.')])),
            ]);
        }// [tl! add:end]

        return redirect()->route('chirps.index')->with('notice', __('Chirp updated.'));
    }
    // [tl! collapse:start]
    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chirp         $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        return redirect()->route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
    // [tl! collapse:end]
}
```

Now, if you try editing a chirp, you should see the same thing as before, but now we're sure that our chirp will just be updated no matter if it's present in the index listing of chirps or not after the form is submitted. Yay!

![Hotwiring Editing Chirps](/images/bootcamp/hotwiring-editing-chirp.png)

## Deleting Chirps with Turbo Streams

If you try deleting a Chirp now that they are wrapped in a `turbo-frame` you'll notice the Chirp itself is gone, but for the wrong reason. That happens because after deleting a Chirp, we're also redirecting users to the index page and it happens that there's no chirp in there because it's gone from the database. Since Turbo didn't find a matching Turbo Frame, it removes the frame's content!

Let's change the `destroy` action in our `ChirpController` to respond with a remove Turbo Stream whenever a Chirp is deleted and the client supports it:

```php filename=app/Controllers/ChirpController.php
<?php
// [tl! collapse:start]
namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
// [tl! collapse:end]
class ChirpController extends Controller
{
    // [tl! collapse:start]
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('chirps.index', [
            'chirps' => Chirp::with('user')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('chirps.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp = $request->user()->chirps()->create($validated);

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps.partials.form')),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp created.')])),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp created.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function edit(Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp->update($validated);

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp updated.')])),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp updated.'));
    }
    // [tl! collapse:end]
    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request [tl! add]
     * @param  \App\Models\Chirp         $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chirp $chirp) // [tl! remove]
    public function destroy(Request $request, Chirp $chirp) // [tl! add]
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        if ($request->wantsTurboStream()) { // [tl! add:start]
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp deleted.')])),
            ]);
        } // [tl! add:end]

        return redirect()->route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
}
```

And that's it!

## Turbo Stream Flash Macro

So far we've been using the default action methods provided by the Turbo Laravel package. Let's add a `notice` macro to the `PendingTurboStreamResponse` class, which the `turbo_stream()` function returns (except when we give it an array, which then it returns an instance of the `MultiplePendingTurboStreamResponse` class). This `notice` macro will work as a shorhand for the creating Turbo Streams to append notifications on the page:

```php filename="app/Providers/AppServiceProvider.php"
<?php

namespace App\Providers;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use Illuminate\Support\ServiceProvider;
use Tonysm\TurboLaravel\Http\PendingTurboStreamResponse; // [tl! add]

class AppServiceProvider extends ServiceProvider
{
    // [tl! collapse:start]
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    // [tl! collapse:end]
    public function boot()
    {
        Turbo::usePartialsSubfolderPattern();
        PendingTurboStreamResponse::macro('notice', function ($message) { // [tl! add:0,5]
            return turbo_stream()->append('notifications', view('layouts.partials.notice', [
                'message' => $message,
            ]));
        });
    }
}
```

Now, our controllers can be cleaned up a bit:

```php filename="app/Http/Controllers/ChirpController.php"
<?php
// [tl! collapse:start]
namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
// [tl! collapse:end]
class ChirpController extends Controller
{
    // [tl! collapse:start]
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('chirps.index', [
            'chirps' => Chirp::with('user:id,name')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('chirps.create');
    }
    // [tl! collapse:end]
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp = $request->user()->chirps()->create($validated);

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps.partials.form')),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp created.')])),
                turbo_stream()->notice(__('Chirp created.')),// [tl! remove:-1,1 add]
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp created.'));
    }
    // [tl! collapse:start]
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function edit(Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }
    // [tl! collapse:end]
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp->update($validated);

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp updated.')])),
                turbo_stream()->notice(__('Chirp updated.')), // [tl! remove:-1,1 add]
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chirp         $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.partials.notice', ['message' => __('Chirp deleted.')])),
                turbo_stream()->notice(__('Chirp deleted.')),// [tl! remove:-1,1 add]
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
}
```

Although this is using Macros, we're still using the Turbo Stream actions that ship with Turbo by default. It's also possible to go custom and create your own actions, if you want to.

## Testing it out

With these changes, our application behaves so much better than before! Try it out yourself!

![Inline Editing Forms](/images/bootcamp/hotwiring-chirps-inline-forms.png)

[Continue to Broadcasting...](/guides/broadcasting)
