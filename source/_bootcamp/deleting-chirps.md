---
extends: _layouts.bootcamp
title: Deleting Chirps
description: Implemeting the deletion of Chirps
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

        return to_route('chirps.index')->with('notice', __('Chirp deleted.'));+}
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
<div class="p-4 flex space-x-4">
    <div>
        <x-profile :initials="$chirp->user->initials()" />
    </div>

    <div class="flex-1">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-base-content font-medium">{{ $chirp->user->name }}</span>
                <small class="ml-2 text-sm text-base-content/50"><x-local-time-ago :value="$chirp->created_at" data-turbo-permanent /></small>
                @unless ($chirp->created_at->eq($chirp->updated_at))
                <small class="text-sm text-base-content/50"> &middot; edited</small>
                @endunless
            </div>

            @if (Auth::id() === $chirp->user->id)
            <x-dropdown class="dropdown-end">
                <x-slot name="trigger" class="btn-ghost btn-xs">
                    <x-heroicon-o-ellipsis-vertical class="size-6" />
                    <span class="sr-only">{{ __('Options') }}</span>
                </x-slot>

                <x-dropdown.menu class="bg-base-200">
                    <x-dropdown.link href="{{ route('chirps.edit', $chirp) }}">{{ __('Edit') }}</x-dropdown.link>

{+                    <form action="{{ route('chirps.destroy', $chirp) }}" method="POST">
                        @method('DELETE')
                        <x-dropdown.button type="submit">{{ __('Delete') }}</x-dropdown.button>
                    </form>+}
                </x-dropdown.menu>
            </x-dropdown>
            @endif
        </div>
        <p class="mt-1 text-base">{{ $chirp->message }}</p>
    </div>
</div>
```

</x-fenced-code>

## Testing it out

If you Chirped anything you weren't happy with, try deleting it!

![Deleting Chirps](/assets/images/bootcamp/deleting-chirps.png?v=2)
