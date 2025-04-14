---
extends: _layouts.bootcamp
title: Hotwiring Everything
description: Make it alive with Turbo Frames and Turbo Streams!
order: 7
---

# *06.* Hotwiring Everything

So far, our application is quite basic. Out of Hotwire, we're only using Turbo Drive, which is enabled by default when we install and start Turbo.

## Inline forms with Turbo Frames

Our application works, but we could improve it. Instead of sending users to a dedicated chirp creation form page, let's display the form inline right on the `chirps.index` page. To do that, we're going to use [lazy-loading Turbo Frames](https://turbo.hotwired.dev/reference/frames):

<x-fenced-code file="resources/views/chirps/index.blade.php">

```blade
<x-layouts.app :title="__('Chirps')">
    <section class="w-full lg:max-w-lg mx-auto">
        <div class="flex items-center space-x-2 justify-between">
            <x-text.heading size="xl">{{ __('Chirps') }}</x-text.heading>

{-            <a href="{{ route('chirps.create') }}" class="btn btn-primary btn-sm">{{ __('Write') }}</a>-}
{+            <a href="{{ route('chirps.create') }}" class="btn btn-primary btn-sm invisible [body:where(:has(#create\_chirp:empty))_&]:visible">{{ __('Write') }}</a>+}
        </div>

{+        <x-turbo::frame id="create_chirp" class="hidden md:block *:mt-4" loading="lazy" src="{{ route('chirps.create') }}"></x-turbo::frame>+}

{-        <div class="mt-6 hotwire-native:mt-0 card hotwire-native:rounded-none hotwire-native:mb-20 bg-base-100 divide-y divide-base-200 shadow">-}
{+        <div id="chirps" class="mt-6 hotwire-native:mt-0 card hotwire-native:rounded-none hotwire-native:mb-20 bg-base-100 divide-y divide-base-200 shadow">+}
            @each('chirps.partials.chirp', $chirps, 'chirp')
        </div>
    </section>
</x-layouts.app>
```

</x-fenced-code>

For that to work, we also need to wrap our create form with a matching Turbo Frame (by "matching" I mean same DOM ID):

<x-fenced-code file="resources/views/chirps/create.blade.php">

```blade
<x-layouts.app :title="__('New Chirp')">
    <section class="w-full lg:max-w-lg mx-auto">
        <x-back-link :href="route('chirps.index')">{{ __('Chirps') }}</x-back-link>
        <x-text.heading size="xl">{{ __('New Chirp') }}</x-text.heading>
        <x-text.subheading>{{ __('Write a message to the World.') }}</x-text.subheading>

        <x-page-card class="my-6">
{-            @include('chirps.partials.form')-}
{+            <x-turbo::frame id="create_chirp" target="_top">
                @include('chirps.partials.form')
            </x-turbo::frame>+}
        </x-page-card>
    </section>
</x-layouts.app>
```

</x-fenced-code>

A few things about this:

1. In the `chirps.index`, we specified the Turbo Frame with the `src` attribute, which indicates Turbo that this is lazy-loading Turbo Frame
1. The Turbo Frame in the `chirps.create` page has a `target="_top"` on it. That's not gonna be used, it's just in case someone opens that page directly by visiting `/chirps/create` or disables JavaScript (in this case, they would still see the link pointing to the create chirps page, so they would be able to use our application normally)

If you try to use the form now, you will see a strange behavior where the form disappears after you submit it and the link is back. If you refresh the page, you'll see the chirp was successfully created.

That happens because we're redirecting users to the `chirps.index` page after the form submission. That page has a matching Turbo Frame, which contains the link. Nothing else on the page changes because of the Turbo Frame contains the page changes to only its fragment.

Let's make use of Turbo Streams to update our form with a clean one and prepend the recently created Chirp to the chirps list.

Before we change the `ChirpController`, let's give our list of chirps wrapper element an ID in the `chirps.index` page:

<x-fenced-code file="resources/views/chirps/create.blade.php">

```blade
<x-layouts.app :title="__('New Chirp')">
    <section class="w-full lg:max-w-lg mx-auto">
        <x-back-link :href="route('chirps.index')">{{ __('Chirps') }}</x-back-link>
        <x-text.heading size="xl">{{ __('New Chirp') }}</x-text.heading>
        <x-text.subheading>{{ __('Write a message to the World.') }}</x-text.subheading>

        <x-page-card class="my-6">
{-            @include('chirps.partials.form')-}
{+            <x-turbo::frame id="create_chirp" target="_top">
                @include('chirps.partials.form')
            </x-turbo::frame>+}
        </x-page-card>
    </section>
</x-layouts.app>
```

</x-fenced-code>

### Resetting the form and prepending Chirps to the list

Okay, now we can change the `store` action in our `ChirpController` to return 3 Turbo Streams if the client supports it, one to update the form with a clean one, another to prepend the new chirp to the list, and another to append the flash message:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

// ...

class ChirpController extends Controller
{
    // ...

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

{-        $request->user()->chirps()->create($validated);-}
{+        $chirp = $request->user()->chirps()->create($validated);

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps.partials.form')),
                turbo_stream()->append('notifications', view('partials.notice', [
                    'message' => __('Chirp created.'),
                ])),
            ]);
        }+}

        return to_route('chirps.index')->with('notice', __('Chirp posted.'));
    }

    // ...
}
```

</x-fenced-code>

Now if you try creating a Chirp, you should see the newly created Chirp at the top of the chirps list, the form should have been cleared, and a flash message showed up.

![Hotwiring Chirps Creation](/assets/images/bootcamp/hotwiring-creating-chirps.png?v=1)

Let's also implement inline editing for our chirps.

## Displaying the edit chirps form inline

To do that, we need to tweak our `chirps.partials.chirp` partial and wrap it with a Turbo Frame. Instead of showing you a long Git diff, replace the existing partial with this one:

<x-fenced-code file="resources/views/chirps/partials/chirp.blade.php">

```blade
{-<div class="p-4 flex space-x-4">-}
{+<x-turbo::frame :id="$chirp" class="p-4 flex space-x-4">+}
    <div>
        <x-profile :initials="$chirp->user->initials()" />
    </div>

    <div class="flex-1">
        <!-- ... -->
    </div>
{-</div>-}
{+</x-turbo::frame>+}
```

</x-fenced-code>

Now, let's also update the `chirps.edit` page to add a wrapping Turbo Frame around the form there:

<x-fenced-code file="resources/views/chirps/edit.blade.php">

```blade
<x-layouts.app :title="__('Edit Chirp')">
    <section class="w-full lg:max-w-lg mx-auto">
        <x-back-link :href="route('chirps.index')">{{ __('Chirps') }}</x-back-link>
        <x-text.heading size="xl">{{ __('Edit Chirp') }}</x-text.heading>
        <x-text.subheading>{{ __('Update your Chirp message.') }}</x-text.subheading>

        <x-page-card class="my-6">
{-            @include('chirps.partials.form')-}
{+            <x-turbo::frame :id="$chirp" target="_top">
                @include('chirps.partials.form')
            </x-turbo::frame>+}
        </x-page-card>
    </section>
</x-layouts.app>
```

</x-fenced-code>

## Updating inline edit form with the chirps partial

Now, if you try clicking on the edit button, you should see the form appearing inline! If you submit it, you will see the change takes place already. That's awesome, right? Well, not so much. See, we can see the change because after the chirp is updated, the controller redirects the user to the index page and it happens that the chirp is rendered on that page, so it finds a matching Turbo Frame. If that wasn't the case, we would see a strange behavior.

Let's change the `update` action in the `ChirpController` to return a Turbo Stream with the updated Chirp partial if the client supports it:

<x-fenced-code file="app/Controllers/ChirpController.php">

```php
<?php

// ...

class ChirpController extends Controller
{
    // ...

    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp->update($validated);

{+        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('partials.notice', [
                    'message' => __('Chirp updated.'),
                ])),
            ]);
        }+}

        return to_route('chirps.index')->with('notice', __('Chirp updated.'));
    }

    // ...
}
```

</x-fenced-code>

Now, if you try editing a chirp, you should see the same thing as before, but now we're sure that our chirp will just be updated no matter if it's present in the index listing of chirps or not after the form is submitted. Yay!

![Hotwiring Editing Chirps](/assets/images/bootcamp/hotwiring-editing-chirp.png?v=4)

## Deleting Chirps with Turbo Streams

If you try deleting a Chirp now that they are wrapped in a `turbo-frame` you'll notice the Chirp itself is gone, but for the wrong reason. That happens because after deleting a Chirp, we're also redirecting users to the index page and it happens that there's no chirp in there because it's gone from the database. Since Turbo didn't find a matching Turbo Frame, it removes the frame's content!

Let's change the `destroy` action in our `ChirpController` to respond with a remove Turbo Stream whenever a Chirp is deleted and the client supports it:

<x-fenced-code file="app/Controllers/ChirpController.php">

```php
<?php

// ...

class ChirpController extends Controller
{
    // ...

    public function destroy(Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

{+        if (request()->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('partials.notice', [
                    'message' => __('Chirp deleted.'),
                ])),
            ]);
        }+}

        return to_route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
}
```

</x-fenced-code>

And that's it!

## Turbo Stream Flash Macro

So far we've been using the default action methods provided by the Turbo Laravel package. Let's add a `notice` macro to the `PendingTurboStreamResponse` class, which the `turbo_stream()` function returns (except when we give it an array, which then it returns an instance of the `MultiplePendingTurboStreamResponse` class). This `notice` macro will work as a shorthand for the creating Turbo Streams to append notifications on the page:

<x-fenced-code file="app/Providers/AppServiceProvider.php">

```php
<?php

namespace App\Providers;

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use Illuminate\Support\ServiceProvider;
{+use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;+}

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Turbo::usePartialsSubfolderPattern();

{+        PendingTurboStreamResponse::macro('notice', function ($message) {
            return turbo_stream()->append('notifications', view('layouts.partials.notice', [
                'message' => $message,
            ]));
        });+}
    }
}
```

</x-fenced-code>

Now, our controllers can be cleaned up a bit:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

// ...

class ChirpController extends Controller
{
    use AuthorizesRequests;

    // ...

    /**
     * Store a newly created resource in storage.
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
{-                turbo_stream()->append('notifications', view('partials.notice', [
                    'message' => __('Chirp posted.'),
                ])),-}
{+                turbo_stream()->notice(__('Chirp posted.')),+}
            ]);
        }

        return to_route('chirps.index')->with('notice', __('Chirp posted.'));
    }

    /**
     * Update the specified resource in storage.
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
{-                turbo_stream()->append('notifications', view('partials.notice', [
                    'message' => __('Chirp updated.'),
                ])),-}
{+                turbo_stream()->notice(__('Chirp updated.')),+}
            ]);
        }

        return to_route('chirps.index')->with('notice', __('Chirp updated.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        if (request()->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
{-                turbo_stream()->append('notifications', view('partials.notice', [
                    'message' => __('Chirp deleted.'),
                ])),-}
{+                turbo_stream()->notice(__('Chirp deleted.')),+}
            ]);
        }

        return to_route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
}
```

</x-fenced-code>

Although this is using Macros, we're still using the Turbo Stream actions that ship with Turbo by default. It's also possible to go custom and create your own actions, if you want to.

## Testing it out

With these changes, our application behaves so much better than before! Try it out yourself!

![Inline Editing Forms](/assets/images/bootcamp/hotwiring-chirps-inline-forms.png?v=4)
