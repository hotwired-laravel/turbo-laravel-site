---
extends: _layouts.bootcamp
title: Broadcasting
description: Broadcasting
order: 8
---

# *07.* Broadcasting

We can send the same Turbo Streams we're returning to our users after a form submission over WebSockets and update the page for all users visiting it! Broadcasts may be triggered automatically whenever a [model updates](https://laravel.com/docs/eloquent#events) or manually whenever you want to broadcast it.

## Setting Up Reverb

Let's setup [Reverb](https://laravel.com/docs/11.x/reverb) to handle our WebSockets connections.

First, run the `install:broadcasting` Artisan command:

```bash
php artisan install:broadcasting
```

When it asks if you want to install the Node dependencies, say "No". After that, we'll install them manually with importamps:

```bash
php artisan importmap:pin laravel-echo pusher-js current.js
```

Next, we'll need to update the published `echo.js` file. It currently uses `import.meta.env.*`, which requires a build step. Instead, we'll update it to use the `current.js` to read the configs from meta tags we'll add to our layouts. But first, replace the `echo.js` with the following version:

<x-fenced-code file="resources/js/echo.js">

```js 
import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

import { Current } from 'current.js';
window.Current = Current;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: Current.reverb.appKey,
    wsHost: Current.reverb.host,
    wsPort: Current.reverb.port ?? 80,
    wssPort: Current.reverb.port ?? 443,
    forceTLS: (Current.reverb.scheme ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

</x-fenced-code>

We also need to update the `bootstrap.js` file to fix the import that was appended by Reverb to the Importmap style:

<x-fenced-code file="resources/js/bootstrap.js">

```js
// ...

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

{-import './echo';-}
{+import 'echo';+}
```

</x-fenced-code>

Next, let's create a new layout partial at `resources/views/layouts/partials/reverb.blade.php` with the following content:

<x-fenced-code file="resources/views/layouts/partials/reverb.blade.php">

```blade
<meta name="current-reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}" />
<meta name="current-reverb-host" content="{{ config('reverb.servers.reverb.frontend.host') }}" />
<meta name="current-reverb-port" content="{{ config('reverb.servers.reverb.frontend.port') }}" />
<meta name="current-reverb-scheme" content="{{ config('reverb.servers.reverb.frontend.scheme') }}" />
```

</x-fenced-code>

Then, add that to the `app.blade.php` layout file:

<x-fenced-code file="resources/views/layouts/app.blade.php">

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @if ($viewTransitions ?? false)
        <meta name="view-transition" content="same-origin" />
        @endif

{+        @include('layouts.partials.reverb')+}

        {{ $meta ?? '' }}
        
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- ... -->
    </head>

    <!-- ... -->
</html>
```

</x-fenced-code>

Do the same for the guest layout:

<x-fenced-code file="resources/views/layouts/guest.blade.php">

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @if ($viewTransitions ?? false)
        <meta name="view-transition" content="same-origin" />
        @endif

{+        @include('layouts.partials.reverb')+}
        
        {{ $meta ?? '' }}

        <!-- ... -->
    </head>

    <!-- ... -->
</html>
```

</x-fenced-code>

Then, we need to tweak our `.env` file to look something like this:

```php
# ...

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=[REDACTED]
REVERB_APP_KEY=[REDACTED]
REVERB_APP_SECRET=[REDACTED]
REVERB_HOST="reverb.test"
REVERB_PORT=8080
REVERB_SCHEME=http

REVERB_FRONTEND_HOST="localhost"
REVERB_FRONTEND_PORT="${REVERB_PORT}"
REVERB_FRONTEND_SCHEME="${REVERB_SCHEME}"
```

With that, our Reverb config needs to be updated to use the new frontend configs:

<x-fenced-code file="config/reverb.php">

```php
<?php

return [
    // ...

    'servers' => [

        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_HOST'),
            'options' => [
                'tls' => [],
            ],
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
            ],
{+            'frontend' => [
                'host' => env('REVERB_FRONTEND_HOST'),
                'port' => env('REVERB_FRONTEND_PORT'),
                'scheme' => env('REVERB_FRONTEND_SCHEME'),
            ],+}
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', 15),
        ],
    ],

    // ...
];
```

</x-fenced-code>

The Broadcasting component has two sides: the backend and the frontend. The backend needs to connect to the Reverb server, and since we're using Sail, we'll spin up a new container for that. For this reason, we cannot use the same host as the frontend, since that's what the browser will use to connect to the server. The backend will connect to a host named `reverb.test:8080` (we'll add it next), and the browser will connect to `localhost:8080`.

If you're following using `artisan serve`, both can be `localhost` or `127.0.0.1`.

Next, update the `docker-compose.yml` to add the new `reverb.test` service:

<x-fenced-code file="docker-compose.yml">

```yaml
services:
    # ...

{+    reverb.test:
        build:
            context: ./vendor/laravel/sail/runtimes/8.3
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        image: sail-8.3/app
        command: ["php", "artisan", "reverb:start"]
        extra_hosts:
            - 'host.docker.internal:host-gateway'
        ports:
            - '${REVERB_PORT:-8080}:8080'
        environment:
            WWWUSER: '${WWWUSER}'
            LARAVEL_SAIL: 1
            XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
            XDEBUG_CONFIG: '${SAIL_XDEBUG_CONFIG:-client_host=host.docker.internal}'
            IGNITION_LOCAL_SITES_PATH: '${PWD}'
        volumes:
            - '.:/var/www/html'
        networks:
            - sail
        depends_on:
            - laravel.test+}

networks:
    sail:
        driver: bridge
```

</x-fenced-code>

Now, we can boot the Reverb service by running:

```bash
./vendor/bin/sail up -d
```

That's it!

## Broadcasting Turbo Streams

Let's start by sending new Chirps to all users currently visiting the chirps page. We're going to start by creating a private broadcasting channel called `chirps` in our `routes/channels.php` file. Any authenticated user may start receiving new Chirps broadcasts when they visit the `chirps.index` page, so we're simply returning `true` in the authorization check:

<x-fenced-code file="routes/channels.php">

```php
<?php

use App\Models\Chirp;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

{+Broadcast::channel('chirps', function () {
    return true;
});+}
```

</x-fenced-code>

Now, let's update the `chirps/index.blade.php` to add the `x-turbo::stream-from` Blade component that ships with Turbo Laravel:

<x-fenced-code file="resources/views/chirps/index.blade.php">

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[__('Chirps')]" />
        </h2>
    </x-slot>

{+    <x-turbo::stream-from source="chirps" />+}

    <div class="py-12">
        <!-- ... -->
    </div>
</x-app-layout>
```

</x-fenced-code>

That's it! When the user visits that page, this component will automatically start listening to a `chirps` _private_ channel for broadcasts. By default, it assumes we're using private channels, but you may configure it to listen to `presence` or `public` channels by passing the `type` prop to the component. In this case, we're passing a string for the channel name, but we could also pass an Eloquent model instance and it would figure out the channel name based on [Laravel's conventions](https://laravel.com/docs/broadcasting#model-broadcasting-conventions).

Now, we're ready to start broadcasting! First, let's add the `Broadcasts` trait to our `Chirp` model:

<x-fenced-code file="app/Models/Chirp.php">

```php
<?php

namespace App\Models;

{+use HotwiredLaravel\TurboLaravel\Models\Broadcasts;+}
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chirp extends Model
{
    use HasFactory;
{+    use Broadcasts;+}

    protected $fillable = [
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

</x-fenced-code>

That trait will give us a bunch of methods we can call from our Chirp model instances. Let's use it in the `store` action of our `ChirpController` to send newly created Chirps to all connected users:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

class ChirpController extends Controller
{
    // ...

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp = $request->user()->chirps()->create($validated);

{+        $chirp->broadcastPrependTo('chirps')
            ->target('chirps')
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();+}

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps._form')),
                turbo_stream()->notice(__('Chirp created.')),
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('notice', __('Chirp created.'));
    }

    // ...
}
```

</x-fenced-code>

To test this, try visiting the `/chirps` page from two different tabs and creating a Chirp in one of them. The other should automatically update! We're also broadcasting on-the-fly in the same request/response life-cycle, which could slow down our response time a bit, depending on your load and your queue driver response time. We can delay the broadcasting (which includes view rendering) to a queued job by chaining the `->later()` method, for example.

Now, let's make sure all visiting users receive Chirp updates whenever it changes. To achieve that, change the `update` action in the `ChirpController`:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

{+use function HotwiredLaravel\TurboLaravel\dom_id;+}

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

{+        $chirp->broadcastReplaceTo('chirps')
            ->target(dom_id($chirp))
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();+}

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp updated.')),
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('notice', __('Chirp updated.'));
    }

    // ...
}
```

</x-fenced-code>

Again, open two tabs, try editing a Chirp and you should see the other tab automatically updating! Cool, right?!

Finally, let's make sure deleted Chirps are removed from all visiting users' pages. Tweak the `destroy` action in the `ChirpController` like so:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

use function HotwiredLaravel\TurboLaravel\dom_id;

class ChirpController extends Controller
{
    // ...

    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

{+        $chirp->broadcastRemoveTo('chirps')
            ->target(dom_id($chirp))
            ->toOthers();+}

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp deleted.')),
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('notice', __('Chirp deleted.'));
    }
}
```

</x-fenced-code>

Now, open two tabs and try deleting a Chirp. You should see it being removed from the other tab as well!

## Automatically Broadcasting on Model Changes

Since we're interested in broadcasting all changes of our Chirp model, we can remove a few lines of code and instruct Turbo Laravel to make that automatically for us.

We may achieve that by setting the `$broadcasts` property to `true` in our `Chirp` model. However, Turbo Laravel will automatically broadcast newly created models using the `append` Turbo Stream action. In our case, we want it to `prepend` instead, so we're setting the `$broadcasts` property to an array and using the `insertsBy` key to configure the creation action to be used.

We also need to override where these broadcasts are going to be sent to. Turbo Laravel will automatically send creates to a channel named using the pluralization of our model's basename, which would work for us. But updates and deletes will be sent to a model's individual channel names (something like `App.Models.Chirp.1` where `1` is the model ID). This is useful because we're usually broadcasting to a parent model's channel via a relationship, which we can do with the `$broadcastsTo` property (see [the docs](/docs/broadcasting#content-broadcasting-model-changes) to know more about this), but in our case we'll always be sending the broadcasts to a private channel named `chirps`.

Our `Chirp` model would end up looking like this:

<x-fenced-code file="app/Models/Chirp.php">

```php
<?php

namespace App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts;
{+use Illuminate\Broadcasting\PrivateChannel;+}
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chirp extends Model
{
    use HasFactory;
    use Broadcasts;

{+    protected $broadcasts = [
        'insertsBy' => 'prepend',
    ];+}

    protected $fillable = [
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

{+    public function broadcastsTo()
    {
        return [
            new PrivateChannel('chirps'),
        ];
    }+}
}
```

</x-fenced-code>

We can then remove a few lines from our `ChirpsController`:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

{-use function HotwiredLaravel\TurboLaravel\dom_id;-}

class ChirpController extends Controller
{
    // ...

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp = $request->user()->chirps()->create($validated);

{-        $chirp->broadcastPrependTo('chirps')
           ->target('chirps')
           ->partial('chirps.partials.chirp', ['chirp' => $chirp])
           ->toOthers();-}

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps._form')),
                turbo_stream()->notice(__('Chirp created.')),
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('notice', __('Chirp created.'));
    }

    // ...

    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp->update($validated);

{-        $chirp->broadcastReplaceTo('chirps')
            ->target(dom_id($chirp))
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();-}

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp updated.')),
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('notice', __('Chirp updated.'));
    }

    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

{-        $chirp->broadcastRemoveTo('chirps')
            ->target(dom_id($chirp))
            ->toOthers();-}

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp deleted.')),
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('notice', __('Chirp deleted.'));
    }
}
```

</x-fenced-code>

> **note**
> We're only covering Turbo Stream broadcasts from an Eloquent model's perspective. However, you may broadcast anything using the `TurboStream` Facade or by chaining the `broadcastTo()` method call when using the `turbo_stream()` response builder function. Check the [Broadcasting docs](/docs/broadcasting#content-handmade-broadcasts) to know more about this.

## Testing it out

Before testing it out, we'll need to start a queue worker. That's because Laravel 11 sets the `QUEUE_CONNECTION=database` by default instead of `sync`, and Turbo Laravel will send automatic broadcasts in background. Let's do that:

```bash
sail artisan queue:work --tries=1
```

Now we can test it and it should be working!

One more cool thing about this approach: users will receive the broadcasts no matter where the Chirp models were created from! We can test this out by creating a Chirp entry from Tinker, for example. To try that, start a new Tinker session:

```bash
php artisan tinker
```

And then create a Chirp from there:

```php
App\Models\User::first()->chirps()->create(['message' => 'Hello from Tinker!'])
# App\Models\Chirp {#7426
#   message: "Hello from Tinker!",
#   user_id: 1,
#   updated_at: "2023-11-26 23:01:00",
#   created_at: "2023-11-26 23:01:00",
#   id: 18,
# }
```

![Broadcasting from Tinker](/assets/images/bootcamp/broadcasting-tinker.png)

### Extra Credit: Fixing The Missing Dropdowns

When creating the Chirp from Tinker, even though we see them appearing on the page, if you look closely, you may notice that the dropdown with the "Edit" and "Delete" buttons is missing. This would also be true if we were using a real queue driver, since it would defer the rendering of the partial to a background queue worker. That's because when we send the broadcasts to run in background, the partial will render without a request and session contexts, so our calls to `Auth::id()` inside of it will always return `null`, which means the dropdown would never render.

Instead of conditionally rendering the dropdown in the server side, let's switch to always rendering them and hide it from our users with a sprinkle of JavaScript instead.

First, let's update our `layouts.partials.current-identity` partial to include a few things about the currently authenticated user when there's one:

<x-fenced-code file="resources/views/layouts/partials/current-identity.blade.php">

```blade
@auth
<meta name="current-identity-id" content="{{ Auth::user()->id }}" />
<meta name="current-identity-name" content="{{ Auth::user()->name }}" />
@endauth
```

</x-fenced-code>

Next, update the `app.blade.php` to include it:

<x-fenced-code file="resources/views/layouts/app.blade.php">

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @if ($viewTransitions ?? false)
        <meta name="view-transition" content="same-origin" />
        @endif

        @include('layouts.partials.reverb')
{+        @include('layouts.partials.current-identity')+}

        {{ $meta ?? '' }}

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- ... -->
    </head>

    <body class="font-sans antialiased">
        <!-- ... -->
    </body>
</html>
```

</x-fenced-code>

Now, we're going to create a new Stimulus controller that is going to be responsible for the dropdown visibility. It should only show it if the currently authenticated user is the creator of the Chirp. First, let's create the controller:

```bash
php artisan stimulus:make visible_to_creator
```

Now, update the Stimulus controller to look like this:

<x-fenced-code file="resources/js/controllers/visible_to_creator_controller.js">

```js
import { Controller } from "@hotwired/stimulus"

// Connects to data-controller="visible-to-creator"
export default class extends Controller {
    static values = {
        id: String,
    }

    static classes = ['hidden']

    connect() {
        this.toggleVisibility()
    }

    toggleVisibility() {
        if (this.idValue == window.Current.identity.id) {
            this.element.classList.remove(...this.hiddenClasses)
        } else {
            this.element.classList.add(...this.hiddenClasses)
        }
    }
}
```

</x-fenced-code>

Now, let's update our `chirps.partials.chirp.blade.php` partial to use this controller instead of handling this in the server-side:

<x-fenced-code file="resources/views/chirps/partials/chirp.blade.php">

```blade
<x-turbo::frame :id="$chirp" class="p-6 flex space-x-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-400 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <!-- ... -->
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

{-            @if (Auth::id() === $chirp->user->id) 
            <x-dropdown align="right" width="48">-}
{+            <x-dropdown align="right" width="48" class="hidden" data-controller="visible-to-creator" data-visible-to-creator-id-value="{{ $chirp->user_id }}" data-visible-to-creator-hidden-class="hidden">+}
                <!-- ... -->
            </x-dropdown>
{-            @endif-}
        </div>
        <p class="mt-4 text-lg text-gray-900 dark:text-gray-200">{{ $chirp->message }}</p>
    </div>
</x-turbo::frame>
```

</x-fenced-code>

Next, we need to tweak our `dropdown.blade.php` Blade component to accept and merge the `class`, `data-controller`, and `data-action` attributes:

<x-fenced-code file="resources/views/components/dropdown.blade.php">

```blade
{-@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])-}
{+@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white', 'dataController' => '', 'dataAction' => ''])+}

<!-- ... -->

{-<div class="relative" data-controller="dropdown" data-action="turbo:before-cache@window->dropdown#closeNow click@window->dropdown#close close->dropdown#close">-}
{+<div {{ $attributes->merge(['class' => 'relative', 'data-controller' => "dropdown {$dataController}", 'data-action' => "turbo:before-cache@window->dropdown#closeNow click@window->dropdown#closeWhenClickedOutside close->dropdown#close:stop {$dataAction}"]) }}>+}
    <!-- ... -->
</div>
```

</x-fenced-code>

Now, if you try creating another user and test this out, you'll see that the dropdown only shows up for the creator of the Chirp!

![Dropdown only shows up for creator](/assets/images/bootcamp/broadcasting-dropdown-fix.png)

This change also makes our entire `_chirp` partial cacheable! We could cache it and only render that when changes are made to the Chirp model using the Chirp's `updated_at` timestamps, for example.

> **warning**
> Hiding the links in the frontend _**MUST NOT**_ be your only protection here. Always ensure users are authorized to perform actions in the server side. We're already doing this in our controller using [Laravel's Authorization Policies](https://laravel.com/docs/authorization#introduction).
