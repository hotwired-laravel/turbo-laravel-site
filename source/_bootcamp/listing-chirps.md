---
extends: _layouts.bootcamp
title: Listing Chirps
description: Let's add the ability to list Chirps on the app
order: 4
---

# *03.* Listing Chirps

In the previous step we added the ability to create Chirps, now we're ready to display them!

## Retrieving the Chirps

Let's update the `index` action our `ChirpController` to pass Chirps from every user to our `chirps.index` page.

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

namespace App\Http\Controllers;

{+use App\Models\Chirp;+}
use Illuminate\Http\Request;

class ChirpController extends Controller
{
    public function index()
    {
        return view('chirps.index', [
{-            //-}
{+            'chirps' => Chirp::with('user')->latest()->get(),+}
        ]);
    }

    // ...
}
```

</x-fenced-code>

Here we've used Eloquent's `with` method to [eager-load](https://laravel.com/docs/eloquent-relationships#eager-loading) every Chirp's associated user's ID and name. We've also used the `latest` scope to return the records in reverse-chronological order.

Returning all Chirps at once won't scale in production. Take a look at Laravel's powerful [pagination](https://laravel.com/docs/pagination) to improve performance.

## Connecting users to Chirps

The Chirp's `user` relationship hasn't been defined yet. To fix this, let's add a new ["belongs to"](https://laravel.com/docs/eloquent-relationships#one-to-many-inverse) relationship to our `Chirp` model:

<x-fenced-code file="app/Models/Chirp.php">

```php
<?php

// ...

class Chirp extends Model
{
    use HasFactory;

    // ...

{+    public function user()
    {
        return $this->belongsTo(User::class);
    }+}
}
```

</x-fenced-code>

This relationship is the inverse of the "has many" relationship we created earlier on the `User` model.

## Displaying The Chirps

Next, update the `chirps.index` view so we can list all Chirps:

<x-fenced-code file="resources/views/chirps/index.blade.php">

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[__('Chirps')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('chirps.partials.new-chirp-trigger')

{+            <div class="mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:divide-gray-500">
                @each('chirps.partials.chirp', $chirps, 'chirp')
            </div>+}
        </div>
    </div>
</x-app-layout>
```

</x-fenced-code>

Finally, let's create a `chirps.partials.chirp` Blade partial to display Chirp. This component will be responsible for displaying an individual Chirp:

<x-fenced-code file="resources/views/chirps/partials/chirp.blade.php" copy>

```blade
<div class="p-6 flex space-x-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-400 -scale-x-100" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>

    <div class="flex-1">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-gray-800 dark:text-gray-200">{{ $chirp->user->name }}</span>
                <small class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $chirp->created_at->diffForHumans() }}</small>
            </div>
        </div>
        <p class="mt-4 text-lg text-gray-900 dark:text-gray-200">{{ $chirp->message }}</p>
    </div>
</div>
```

</x-fenced-code>

Now take a look in your browser to see the message you Chirped earlier!

![Showing Chirps](/assets/images/bootcamp/showing-chirps.png?v=4)

## Extra Credit: Relative Dates

Right now our `chirp.blade.php` partial formats the date as relative, but that's relative to the time it was rendered, not the current time. We can write it in a way that it would auto-update without requiring a page refresh using [Local Time Laravel](https://github.com/tonysm/local-time-laravel) package.

First, let's install it via Composer:

```bash
composer require tonysm/local-time-laravel
```

Then, install JS package:
```bash
php artisan importmap:pin local-time
```

Now, let's create our own lib setup file in the `libs/localtime.js` file:

<x-fenced-code file="resources/js/libs/local-time.js" copy>

```js
import LocalTime from "local-time"
LocalTime.start()
```

</x-fenced-code>

Next, update the `libs/index.js` file to import it:

<x-fenced-code file="resources/js/libs/index.js">

```js
import "libs/turbo";
{+import "libs/local-time";+}
import "controllers";
```

</x-fenced-code>

Then we can use this package's component in our `chirps._chirp` Blade partial to display relative dates using the newly installed HTML elements:

<x-fenced-code file="resources/views/chirps/partials/chirp.blade.php">

```blade
<div class="p-6 flex space-x-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-400 -scale-x-100" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>

    <div class="flex-1">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-gray-800 dark:text-gray-200">{{ $chirp->user->name }}</span>
{-                <small class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $chirp->created_at->diffForHumans() }}</small>-}
{+                <small class="ml-2 text-sm text-gray-600 dark:text-gray-400"><x-local-time-ago :value="$chirp->created_at" /></small>+}
            </div>
        </div>
        <p class="mt-4 text-lg text-gray-900 dark:text-gray-200">{{ $chirp->message }}</p>
    </div>
</div>
```

</x-fenced-code>

If you refresh the page, you should see the date string and it quickly updates to the relative time ago. The real nice thing about this approach is that if you keep your browser tab opened while visiting the listing Chirps page, the relative time will update from time to time!
