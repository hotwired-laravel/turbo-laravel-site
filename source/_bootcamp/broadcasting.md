---
extends: _layouts.bootcamp
title: Broadcasting
description: Broadcasting
order: 10
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

We also need to update the `bootstrap.js` file to fix the import that was appended by Reverb to the Importmap style:

```js
// ...

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
import 'echo'; // [tl! remove:-1,1 add]
```

Next, let's create a new layout partial at `resources/views/layouts/partials/reverb.blade.php` with the following content:

```blade
<meta name="current-reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}" />
<meta name="current-reverb-host" content="{{ config('reverb.servers.reverb.frontend.host') }}" />
<meta name="current-reverb-port" content="{{ config('reverb.servers.reverb.frontend.port') }}" />
<meta name="current-reverb-scheme" content="{{ config('reverb.servers.reverb.frontend.scheme') }}" />
```

Then, add that to the `app.blade.php` layout file:

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
        {{ $meta ?? '' }}
        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- ... --}}
    </head>

    {{-- ... --}}
</html>
```

Do the same for the guest layout.

Then, we need to tweak our `.env` file to look something like this:

```env
# [tl! collapse:start]
APP_NAME=Laravel
APP_ENV=local
APP_KEY=[REDACTED]
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=turbo_chirper_l11
# DB_USERNAME=sail
# DB_PASSWORD=password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
# [tl! collapse:end]
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

```php filename="config/reverb.php"
<?php

return [
    // [tl! collapse:start]
    /*
    |--------------------------------------------------------------------------
    | Default Reverb Server
    |--------------------------------------------------------------------------
    |
    | This option controls the default server used by Reverb to handle
    | incoming messages as well as braodcasting message to all your
    | connected clients. At this time only "reverb" is supported.
    |
    */

    'default' => env('REVERB_SERVER', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Reverb Servers
    |--------------------------------------------------------------------------
    |
    | Here you may define details for each of the supported Reverb servers.
    | Each server has its own configuration options that are defined in
    | the array below. You should ensure all the options are present.
    |
    */
    // [tl! collapse:end]
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
            'frontend' => [ // [tl! add:start]
                'host' => env('REVERB_FRONTEND_HOST'),
                'port' => env('REVERB_FRONTEND_PORT'),
                'scheme' => env('REVERB_FRONTEND_SCHEME'),
            ], // [tl! add:end]
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', 15),
        ],

    ],
    // [tl! collapse:start]
    /*
    |--------------------------------------------------------------------------
    | Reverb Applications
    |--------------------------------------------------------------------------
    |
    | Here you may define how Reverb applications are managed. If you choose
    | to use the "config" provider, you may define an array of apps which
    | your server will support, including their connection credentials.
    |
    */

    'apps' => [

        'provider' => 'config',

        'apps' => [
            [
                'key' => env('REVERB_APP_KEY'),
                'secret' => env('REVERB_APP_SECRET'),
                'app_id' => env('REVERB_APP_ID'),
                'options' => [
                    'host' => env('REVERB_HOST'),
                    'port' => env('REVERB_PORT', 443),
                    'scheme' => env('REVERB_SCHEME', 'https'),
                    'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
                ],
                'allowed_origins' => ['*'],
                'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
                'max_message_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10000),
            ],
        ],

    ],
    // [tl! collapse:end]
];
```

The Broadcasting component has two sides: the backend and the frontend. The backend needs to connect to the Reverb server, and since we're using Sail, we'll spin up a new container for that. For this reason, we cannot use the same host as the frontend, since that's what the browser will use to connect to the server. The backend will connect to a host named `reverb.test:8080` (we'll add it next), and the browser will connect to `localhost:8080`.

If you're following using `artisan serve`, both can be `localhost` or `127.0.0.1`.

Next, update the `docker-compose.yml` to add the new `reverb.test` service:

```yaml filename="docker-compose.yml"
services:
    # [tl! collapse:start]
    laravel.test:
        build:
            context: ./vendor/laravel/sail/runtimes/8.3
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        image: sail-8.3/app
        extra_hosts:
            - 'host.docker.internal:host-gateway'
        ports:
            - '${APP_PORT:-80}:80'
            - '${VITE_PORT:-5173}:${VITE_PORT:-5173}'
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
        depends_on: {  }
    # [tl! collapse:end]
    reverb.test: # [tl! add:start]
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
            - laravel.test # [tl! add:end]
networks:
    sail:
        driver: bridge
```

Now, we can boot the Reverb service by running:

```bash
./vendor/bin/sail up -d
```

That's it!

## Broadcasting Turbo Streams

Let's start by sending new Chirps to all users currently visiting the chirps page. We're going to start by creating a private broadcasting channel called `chirps` in our `routes/channels.php` file. Any authenticated user may start receiving new Chirps broadcasts when they visit the `chirps.index` page, so we're simply returning `true` in the authorization check:

```php filename="routes/channels.php"
<?php

use App\Models\Chirp;
use Illuminate\Support\Facades\Broadcast;
// [tl! collapse:start]
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
// [tl! collapse:end]
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
// [tl! add:1,3]
Broadcast::channel('chirps', function () {
    return true;
});
```

Now, let's update the `chirps/index.blade.php` to add the `x-turbo::stream-from` Blade component that ships with Turbo Laravel:

```blade filename="resources/views/chirps/index.blade.php"
<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[__('Chirps')]" />
        </h2>
    </x-slot>

    <x-turbo::stream-from source="chirps" /> <!-- [tl! add] -->

    <div class="py-12">
        <!-- [tl! collapse:start] -->
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    <x-turbo::frame id="create_chirp" src="{{ route('chirps.create') }}">
                        @include('chirps.partials.new-chirp-trigger')
                    </x-turbo::frame>

                    <div id="chirps" class="mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:divide-gray-500">
                        @each('chirps._chirp', $chirps, 'chirp')
                    </div>
                </div>
            </div>
        </div>
        <!-- [tl! collapse:end] -->
    </div>
</x-app-layout>
```

That's it! When the user visits that page, this component will automatically start listening to a `chirps` _private_ channel for broadcasts. By default, it assumes we're using private channels, but you may configure it to listen to `presence` or `public` channels by passing the `type` prop to the component. In this case, we're passing a string for the channel name, but we could also pass an Eloquent model instance and it would figure out the channel name based on [Laravel's conventions](https://laravel.com/docs/broadcasting#model-broadcasting-conventions).

Now, we're ready to start broadcasting! First, let's add the `Broadcasts` trait to our `Chirp` model:

```php filename="app/Models/Chirp.php"
<?php

namespace App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts; // [tl! add]
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chirp extends Model
{
    use HasFactory;
    use Broadcasts; // [tl! add]

    protected $fillable = [
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

That trait will give us a bunch of methods we can call from our Chirp model instances. Let's use it in the `store` action of our `ChirpController` to send newly created Chirps to all connected users:

```php filename="app/Http/Controllers/ChirpController.php"
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
        return view('chirps.create', [
            //
        ]);
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
        // [tl! add:1,4]
        $chirp->broadcastPrependTo('chirps')
            ->target('chirps')
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps._form')),
                turbo_stream()->notice(__('Chirp created.')),
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
                turbo_stream()->notice(__('Chirp updated.')),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp deleted.')),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
    // [tl! collapse:end]
}
```

To test this, try visiting the `/chirps` page from two different tabs and creating a Chirp in one of them. The other should automatically update! We're also broadcasting on-the-fly in the same request/response life-cycle, which could slow down our response time a bit, depending on your load and your queue driver response time. We can delay the broadcasting (which includes view rendering) to the a queued job by chaining the `->later()` method, for example.

Now, let's make sure all visiting users receive Chirp updates whenever it changes. To achieve that, change the `update` action in the `ChirpController`:

```php filename="app/Http/Controllers/ChirpController.php"
<?php
// [tl! collapse:start]
namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
// [tl! collapse:end add:1,1]
use function HotwiredLaravel\TurboLaravel\dom_id;

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
        return view('chirps.create', [
            //
        ]);
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

        $chirp->broadcastPrependTo('chirps')
            ->target('chirps')
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps._form')),
                turbo_stream()->notice(__('Chirp created.')),
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
        // [tl! add:1,4]
        $chirp->broadcastReplaceTo('chirps')
            ->target(dom_id($chirp))
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp updated.')),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp updated.'));
    }
    // [tl! collapse:start]
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp deleted.')),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
    // [tl! collapse:end]
}
```

Again, open two tabs, try editing a Chirp and you should see the other tab automatically updating! Cool, right?!

Finally, let's make sure deleted Chirps are removed from all visiting users' pages. Tweak the `destroy` action in the `ChirpController` like so:

```php filename="app/Http/Controllers/ChirpController.php"
<?php
// [tl! collapse:start]
namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

use function HotwiredLaravel\TurboLaravel\dom_id;
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
        return view('chirps.create', [
            //
        ]);
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

        $chirp->broadcastPrependTo('chirps')
            ->target('chirps')
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps.partials.chirp-form')),
                turbo_stream()->notice(__('Chirp created.')),
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

        $chirp->broadcastReplaceTo('chirps')
            ->target(dom_id($chirp))
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp updated.')),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp updated.'));
    }
    // [tl! collapse:end]
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();
        // [tl! add:1,3]
        $chirp->broadcastRemoveTo('chirps')
            ->target(dom_id($chirp))
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp deleted.')),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
}
```

Now, open two tabs and try deleting a Chirp. You should see it being removed from the other tab as well!

## Automatically Broadcasting on Model Changes

Since we're interested in broadcasting all changes of our Chirp model, we can remove a few lines of code and instruct Turbo Laravel to make that automatically for us.

We may achieve that by setting the `$broadcasts` property to `true` in our `Chirp` model. However, Turbo Laravel will automatically broadcast newly created models using the `append` Turbo Stream action. In our case, we want it to `prepend` instead, so we're setting the `$broadcasts` property to an array and using the `insertsBy` key to configure the creation action to be used.

We also need to override where these broadcasts are going to be sent to. Turbo Laravel will automatically send creates to a channel named using the pluralization of our model's basename, which would work for us. But updates and deletes will be sent to a model's individual channel names (something like `App.Models.Chirp.1` where `1` is the model ID). This is useful because we're usually broadcasting to a parent model's channel via a relationship, which we can do with the `$broadcastsTo` property (see [the docs](https://turbo-laravel.com/docs/1.x/broadcasting#content-broadcasting-model-changes) to know more about this), but in our case we'll always be sending the broadcasts to a private channel named `chirps`.

Our `Chirp` model would end up looking like this:

```php filename="app/Models/Chirp.php"
<?php

namespace App\Models;

use HotwiredLaravel\TurboLaravel\Models\Broadcasts;
use Illuminate\Broadcasting\PrivateChannel; // [tl! add]
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chirp extends Model
{
    use HasFactory;
    use Broadcasts;
    // [tl! add:1,3]
    protected $broadcasts = [
        'insertsBy' => 'prepend',
    ];

    protected $fillable = [
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // [tl! add:1,6]
    public function broadcastsTo()
    {
        return [
            new PrivateChannel('chirps'),
        ];
    }
}
```

We can then remove a few lines from our `ChirpsController`:

```php filename="app/Http/Controllers/ChirpController.php"
<?php
// [tl! collapse:start]
namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
// [tl! collapse:end remove:1,1]
use function HotwiredLaravel\TurboLaravel\dom_id;

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
        return view('chirps.create', [
            //
        ]);
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
        // [tl! remove:1,4]
        $chirp->broadcastPrependTo('chirps')
            ->target('chirps')
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps._form')),
                turbo_stream()->notice(__('Chirp created.')),
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
        // [tl! remove:1,4]
        $chirp->broadcastReplaceTo('chirps')
            ->target(dom_id($chirp))
            ->partial('chirps.partials.chirp', ['chirp' => $chirp])
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp updated.')),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();
        // [tl! remove:1,3]
        $chirp->broadcastRemoveTo('chirps')
            ->target(dom_id($chirp))
            ->toOthers();

        if ($request->wantsTurboStream()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->notice(__('Chirp deleted.')),
            ]);
        }

        return redirect()->route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
}
```

> **note**
> We're only covering Turbo Stream broadcasts from an Eloquent model's perspective. However, you may broadcast anything using the `TurboStream` Facade or by chaining the `broadcastTo()` method call when using the `turbo_stream()` response builder function. Check the [Broadcasting docs](https://turbo-laravel.com/docs/1.x/broadcasting#content-handmade-broadcasts) to know more about this.

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

![Broadcasting from Tinker](/images/bootcamp/broadcasting-tinker.png)

### Extra Credit: Fixing The Missing Dropdowns

When creating the Chirp from Tinker, even though we see them appearing on the page, if you look closely, you may notice that the dropdown with the "Edit" and "Delete" buttons is missing. This would also be true if we were using a real queue driver, since it would defer the rendering of the partial to a background queue worker. That's because when we send the broadcasts to run in background, the partial will render without a request and session contexts, so our calls to `Auth::id()` inside of it will always return `null`, which means the dropdown would never render.

Instead of conditionally rendering the dropdown in the server side, let's switch to always rendering them and hide it from our users with a sprinkle of JavaScript instead.

First, let's update our `layouts.partials.current-identity` partial to include a few things about the currently authenticated user when there's one:

```blade filename="resources/views/layouts/partials/current-identity.blade.php"
@auth
<meta name="current-identity-id" content="{{ Auth::user()->id }}" />
<meta name="current-identity-name" content="{{ Auth::user()->name }}" />
@endauth
```

Next, update the `app.blade.php` to include it:

```blade filename="resources/views/layouts/app.blade.php"
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
        @include('layouts.partials.current-identity')
        {{ $meta ?? '' }}
        <!-- [tl! add:-2,1 collapse:start] -->

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link rel="stylesheet" href="{{ tailwindcss('css/app.css') }}">

        <!-- Scripts -->
        <x-importmap::tags />
        <!-- [tl! collapse:end] -->
    </head>
    <!-- [tl! collapse:start] -->
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.partials.navigation')
            @include('layouts.partials.notifications')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
    <!-- [tl! collapse:end] -->
</html>
```

Now, we're going to create a new Stimulus controller that is going to be responsible for the dropdown visibilily. It should only show it if the currently authenticated user is the creator of the Chirp. First, let's create the controller:

```bash
php artisan stimulus:make visible_to_creator
```

Now, update the Stimulus controller to look like this:

```js filename="resources/js/controllers/visible_to_creator_controller.js"
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

Now, let's update our `chirps.partials.chirp.blade.php` partial to use this controller instead of handling this in the server-side:

```blade filename="resources/views/chirps/partials/chirp.blade.php"
<x-turbo::frame :id="$chirp" class="p-6 flex space-x-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-400 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <!-- [tl! collapse:start] -->
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        <!-- [tl! collapse:end] -->
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

            @if (Auth::id() === $chirp->user->id) <!-- [tl! remove:0,2 add:2,1] -->
            <x-dropdown align="right" width="48">
            <x-dropdown align="right" width="48" class="hidden" data-controller="visible-to-creator" data-visible-to-creator-id-value="{{ $chirp->user_id }}" data-visible-to-creator-hidden-class="hidden">
                <!-- [tl! collapse:start] -->
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
                <!-- [tl! collapse:end] -->
            </x-dropdown>
            @endif <!-- [tl! remove] -->
        </div>
        <p class="mt-4 text-lg text-gray-900 dark:text-gray-200">{{ $chirp->message }}</p>
    </div>
</x-turbo::frame>
```

Next, we need to tweak our `dropdown.blade.php` Blade component to accept and merge the `class`, `data-controller`, and `data-action` attributes:

```blade filename="resources/views/components/dropdown.blade.php"
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white', 'dataController' => '', 'dataAction' => ''])
<!-- [tl! remove:-2,1 add:-1,1 collapse:start] -->
@php
switch ($align) {
    case 'left':
        $alignmentClasses = 'origin-top-left left-0';
        break;
    case 'top':
        $alignmentClasses = 'origin-top';
        break;
    case 'right':
    default:
        $alignmentClasses = 'origin-top-right right-0';
        break;
}

switch ($width) {
    case '48':
        $width = 'w-48';
        break;
}
@endphp
<!-- [tl! collapse:end remove:1,1 add:2,1] -->
<div class="relative" data-controller="dropdown" data-action="turbo:before-cache@window->dropdown#closeNow click@window->dropdown#close close->dropdown#close">
<div {{ $attributes->merge(['class' => 'relative', 'data-controller' => "dropdown {$dataController}", 'data-action' => "turbo:before-cache@window->dropdown#closeNow click@window->dropdown#closeWhenClickedOutside close->dropdown#close:stop {$dataAction}"]) }}>
    <!-- [tl! collapse:start] -->
    <div data-action="click->dropdown#toggle" data-dropdown-target="trigger">
        {{ $trigger }}
    </div>

    <div
        data-dropdown-target="menu"
        data-transition-enter="transition ease-out duration-200"
        data-transition-enter-start="transform opacity-0 scale-95"
        data-transition-enter-end="transform opacity-100 scale-100"
        data-transition-leave="transition ease-in duration-75"
        data-transition-leave-start="transform opacity-100 scale-100"
        data-transition-leave-end="transform opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }} hidden"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
    <!-- [tl! collapse:end] -->
</div>
```

Now, if you try creating another user and test this out, you'll see that the dropdown only shows up for the creator of the Chirp!

![Dropdown only shows up for creator](/images/broadcasting-dropdown-fix.png)

This change also makes our entire `_chirp` partial cacheable! We could cache it and only render that when changes are made to the Chirp model using the Chirp's `updated_at` timestamps, for example.

> **warning**
> Hiding the links in the frontend _**MUST NOT**_ be your only protection here. Always ensure users are authorized to perform actions in the server side. We're already doing this in our controller using [Laravel's Authorization Policies](https://laravel.com/docs/authorization#introduction).
