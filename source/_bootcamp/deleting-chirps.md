---
extends: _layouts.bootcamp
title: Deleting Chirps
description: Deleting Chirps
order: 6
---

# *05.* Deleting Chirps

Sometimes no amount of editing can fix a message, so let's give our users the ability to delete their Chirps.

Hopefully you're starting to get the hang of things now. We think you'll be impressed how quickly we can add this feature.

## Routing

Let's update our `routes/web.php` file to add the new `destroy` action in our resource definition:

<x-fenced-code file="routes/web.php">

```php
// ...

Route::resource('chirps', ChirpController::class)
{-    ->only(['index', 'create', 'store', 'edit', 'update'])-}
{+    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])+}
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
DELETE    | `/chirps/{chirp}`      | destroy      | `chirps.destroy`

## Updating the Controller

Now we can update the `destroy` action on our `ChirpController` class to perform the deletion and return to the Chirp index:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

// ...

class ChirpController extends Controller
{
    // ...

    public function destroy(Chirp $chirp)
    {
{-        //-}
{+        $this->authorize('delete', $chirp);

        $chirp->delete();

        return redirect()
            ->route('chirps.index')
            ->with('notice', __('Chirp deleted.'));+}
    }
}
```

</x-fenced-code>

## Authorization

As with editing, we only want our Chirp authors to be able to delete their Chirps, so let's update the `delete` method our `ChirpPolicy` class:

<x-fenced-code file="app/Policies/ChirpPolicy.php">

```php
<?php

// ...

class ChirpPolicy
{
    // ...

    public function delete(User $user, Chirp $chirp)
    {
{-        //-}
{+        return $user->is($chirp->user);+}
    }

    // ...
}
```

</x-fenced-code>

Although the logic of authorizing users to update or delete Chirps is pretty much the same for this demo app, chances are you may have different authorization policies in a real app. For that reason, we're leaving them separate.

## Updating our Chirp partial

Finally, we can add a delete button to the dropdown menu we created earlier in our `chirps._chirp` Blade partial:

<x-fenced-code file="resources/views/chirps/partials/chirp.blade.php">

```blade
<div class="p-6 flex space-x-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-400 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>

    <div class="flex-1">
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

{+                    <form action="{{ route('chirps.destroy', $chirp) }}" method="POST">
                        @method('DELETE')
                        <x-dropdown-button type="submit">{{ __('Delete') }}</x-dropdown-button>
                    </form>+}
                </x-slot>
            </x-dropdown>
            @endif
        </div>

        <p class="mt-4 text-lg text-gray-900">{{ $chirp->message }}</p>
    </div>
</div>
```

</x-fenced-code>

## Testing it out

If you Chirped anything you weren't happy with, try deleting it!

![Deleting Chirps](/assets/images/bootcamp/deleting-chirps.png)
