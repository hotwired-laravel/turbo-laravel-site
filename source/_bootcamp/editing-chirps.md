---
extends: _layouts.bootcamp
title: Editing Chirps
description: Allowing users to edit their Chirps
order: 5
---

# *04.* Editing Chirps

Let's add a feature that's missing from other popular bird-themed microblogging platforms — the ability to edit Chirps!

## Routing

First we will update our routes file to enable the `chirps.edit` and `chirps.update` routes for our resource controller:

<x-fenced-code file="routes/web.php">

```php
<?php

// ...

Route::resource('chirps', ChirpController::class)
{-    ->only(['index', 'create', 'store'])-}
{+    ->only(['index', 'create', 'store', 'edit', 'update'])+}
    ->middleware(['auth', 'verified']);

// ...

```

</x-fenced-code>

Our route table for this controller now looks like this:

Verb      | URI                    | Action       | Route Name
----------|------------------------|--------------|---------------------
GET       | `/chirps`              | index        | `chirps.index`
GET       | `/chirps/create`       | create       | `chirps.create`
POST      | `/chirps`              | store        | `chirps.store`
GET       | `/chirps/{chirp}/edit` | edit         | `chirps.edit`
PUT/PATCH | `/chirps/{chirp}`      | update       | `chirps.update`

## Updating our partial

Next, let's update our `chirps.partials.chirp` Blade partial to have an edit form for existing Chirps.

We're going to use the `<x-dropdown>` component that comes with Turbo Breeze, which we'll only display to the Chirp author. We'll also display an indication if a Chirp has been edited by comparing the Chirp's `created_at` date with its `updated_at` date:

<x-fenced-code file="resources/views/chirps/partials/chirp.blade.php">

```blade
<div class="p-4 flex space-x-4">
    <div>
        <x-profile :initials="$chirp->user->initials()" />
    </div>

    <div class="flex-1">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-base-content font-medium">{{ $chirp->user->name }}</span>
                <small class="ml-2 text-sm text-base-content/50"><x-local-time-ago :value="$chirp->created_at" data-turbo-permanent /></small>
{+                @unless ($chirp->created_at->eq($chirp->updated_at))
                <small class="text-sm text-base-content/50"> &middot; edited</small>
                @endunless+}
            </div>

{+            @if (auth()->user()->id === $chirp->user->id)
            <x-dropdown class="dropdown-end">
                <x-slot name="trigger" class="btn-ghost btn-xs">
                    <x-heroicon-o-ellipsis-vertical class="size-6" />
                    <span class="sr-only">{{ __('Options') }}</span>
                </x-slot>

                <x-dropdown.menu class="bg-base-200">
                    <x-dropdown.link href="{{ route('chirps.edit', $chirp) }}">{{ __('Edit') }}</x-dropdown.link>
                </x-dropdown.menu>
            </x-dropdown>
            @endif+}
        </div>
        <p class="mt-1 text-base">{{ $chirp->message }}</p>
    </div>
</div>
```

</x-fenced-code>

## Showing the edit Chirp form

We can now update the `edit` action on our `ChirpController` class to show the form to edit Chirps. Even though we're only displaying the edit button to the author of the Chirp, we also need to authorize the request to make sure it's actually the author that is updating it:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
{+use Illuminate\Foundation\Auth\Access\AuthorizesRequests;+}
use Illuminate\Http\Request;

class ChirpController extends Controller
{
{+    use AuthorizesRequests;+}

    // ...

    public function edit(Chirp $chirp)
    {
{-        //-}
{+        $this->authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);+}
    }

    // ...
}
```

</x-fenced-code>

Now, we need to create our `chirps.edit` view:

<x-fenced-code file="resources/views/chirps/edit.blade.php" copy>

```blade
<x-layouts.app :title="__('Edit Chirp')">
    <section class="w-full lg:max-w-lg mx-auto">
        <x-back-link :href="route('chirps.index')">{{ __('Chirps') }}</x-back-link>
        <x-text.heading size="xl">{{ __('Edit Chirp') }}</x-text.heading>
        <x-text.subheading>{{ __('Update your Chirp message.') }}</x-text.subheading>

        <x-page-card class="my-6">
            @include('chirps.partials.form', ['chirp' => $chirp])
        </x-page-card>
    </section>
</x-layouts.app>
```

</x-fenced-code>

We're using the same `form` partial the create chirps view uses. However, in this case we're passing down a Chirp model to the form so it can pre-fill the message field. Since we're passing a Chirp model, we can make the form submit to the `chirps.update` endpoint instead of the default `chirps.store` one when no Chirp is passed. Let's make the changes to the `chirps.partials.form` partial, replace the existing form with this one:

<x-fenced-code file="resources/views/chirps/partials/form.blade.php" copy>

```blade
<form action="{{ ($chirp ?? false) ? route('chirps.update', $chirp) : route('chirps.store') }}" method="post" class="w-full space-y-6">
    @csrf
    @if ($chirp ?? false)
        @method('PUT')
    @endif

    <div>
        <x-form.label for="message">{{ __("What's on your mind?") }}</x-form.label>

        <x-form.textarea-input
            id="message"
            name="message"
            :value="old('message', $chirp?->message ?? '')"
            :data-error="$errors->has('message')"
            required
            autofocus
            autocomplete="off"
            :placeholder="strip_tags(Illuminate\Foundation\Inspiring::quote())"
            class="mt-2"
        />

        <x-form.error :message="$errors->first('content')" />
    </div>

    <div class="flex items-center justify-start gap-4">
        <x-form.button.primary type="submit">{{ __('Post') }}</x-form.button.primary>
        @if ($chirp ?? false)
        <a href="{{ route('chirps.index') }}" class="dark:text-gray-400">{{ __('Cancel') }}</a>
        @endif
    </div>
</form>
```

</x-fenced-code>

## Authorization

By default, the `authorize` method will prevent everyone from being able to update the Chirp. We can specify who is allowed to update it by creating a [Model Policy](https://laravel.com/docs/authorization#creating-policies) with the following command:

```bash
php artisan make:policy ChirpPolicy --model=Chirp
```

This will create a policy class at `app/Policies/ChirpPolicy.php` which we can update to specify that only the author is authorized to update a Chirp:

<x-fenced-code file="app/Policies/ChirpPolicy.php">

```php
<?php

namespace App\Policies;

use App\Models\Chirp;App\
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChirpPolicy
{
    // ...

    public function update(User $user, Chirp $chirp)
    {
{-        //-}
{+        return $user->is($chirp->user);+}
    }

    // ...
}
```

</x-fenced-code>

You should be able to use the dropdown, click on the Edit link and view the edit Chirp form:

![Edit Chirp Form](/assets/images/bootcamp/editing-chirps-form.png?v=4)

## Updating Chirps

We can now change the `update` action on our `ChirpController` class to validate the request and update the database. Again, we also need to authorize the request here to make sure it's actually the author that is updating it:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ChirpController extends Controller
{
    // ...

    public function update(Request $request, Chirp $chirp)
    {
{-        //-}
{+        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp->update($validated);

        return redirect(route('chirps.index'))->with('notice', __('Chirp updated.'));+}
    }

    // ...
}
```

</x-fenced-code>

You may have noticed the validation rules are duplicated with the `store` action. You might consider extracting them using Laravel's [Form Request Validation](https://laravel.com/docs/validation#form-request-validation), which makes it easy to re-use validation rules and to keep your controllers light.

## Testing it out

Time to test it out! Go ahead and edit a few Chirps using the dropdown menu. If you register another user account, you'll see that only the author of a Chirp can edit it.

![Changed Chirps](/assets/images/bootcamp/editing-chirps-changed.png?v=4)
