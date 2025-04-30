---
extends: _layouts.bootcamp
title: Broadcasting
description: Broadcasting with Turbo Streams!
order: 8
---

# *07.* Broadcasting

We can send the same Turbo Streams we're returning to our users after a form submission over WebSockets and update the page for all users visiting it! Broadcasts may be triggered automatically whenever a [model updates](https://laravel.com/docs/eloquent#events) or manually whenever you want to broadcast it.

## Setting Up Reverb

Let's setup [Reverb](https://laravel.com/docs/11.x/reverb) to handle our WebSockets connections.

First, run the `install:broadcasting` Artisan command:

```bash
php artisan install:broadcasting --without-node
```

When it asks if you wan to install Reverb, answer "Yes". After that, we'll install the JS dependencies with importamps:

```bash
php artisan importmap:pin laravel-echo pusher-js current.js
```

Next, we'll need to update the published `echo.js` file. It currently uses `import.meta.env.*`, which requires a build step. Instead, we'll update it to use the `current.js` to read the configs from meta tags we'll add to our layouts. But first, replace the `echo.js` with the following version:

<x-fenced-code file="resources/js/echo.js" copy>

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

We also need to update the `app.js` file to fix the import that was appended by Reverb to the Importmap style:

<x-fenced-code file="resources/js/app.js">

```js
{+import "echo";+}
import "elements/turbo-echo-stream-tag";
import "libs";
```

</x-fenced-code>

Next, let's create a new layout partial at `resources/views/partials/reverb.blade.php` with the following content:

<x-fenced-code file="resources/views/partials/reverb.blade.php" copy>

```blade
<meta name="current-reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}" />
<meta name="current-reverb-host" content="{{ app()->environment('local') && Turbo::isHotwireNativeVisit() ? '10.0.2.2' : config('broadcasting.connections.reverb.options.host') }}" />
<meta name="current-reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}" />
<meta name="current-reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme') }}" />
```

</x-fenced-code>

Then, add that to the `head.blade.php` layout file partials:

<x-fenced-code file="resources/views/partials/head.blade.php">

```blade
<meta charset="utf-8" />
<meta name="viewport"
    content="width=device-width, initial-scale=1.0{{ $scalable ?? false ? ', maximum-scale=1.0, user-scalable=0' : '' }}" />
<meta name="csrf-token" content="{{ csrf_token() }}">

@if ($transitions ?? false)
    <meta name="view-transition" content="same-origin">
@endif

{+@include('partials.reverb')+}

{{ $meta ?? '' }}

<title>{{ $title ?? config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<link href="{{ tailwindcss('css/app.css') }}" rel="stylesheet" />

<x-importmap::tags />
```

</x-fenced-code>

The `head.blade.php` partial file is also included in the auth template.

Now, make sure your `.env` file has the following configs:

```bash
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=[REDACTED]
REVERB_APP_KEY=[REDACTED]
REVERB_APP_SECRET=[REDACTED]
REVERB_HOST="127.0.0.1"
REVERB_PORT="8080"
REVERB_SCHEME=http
```

That's all we need to configure Reverb. We may start the Reverb server process by running the Artisan command in a new terminal window:

```bash
php artisan reverb:start
```

If you're using [Solo](https://github.com/soloterm/solo), update the `config/solo.php` to add the new Reverb command:

<x-fenced-code file="config/solo.php">

```php
<?php

// ...

return [
    // ...

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    */
    'commands' => [
        'HTTP' => 'php artisan serve --host=0.0.0.0 --no-reload',
        'Logs' => EnhancedTailCommand::file(storage_path('logs/laravel.log')),
        'Tailwind' => 'php artisan tailwindcss:watch',
        {+'Reverb' => 'php artisan reverb:start --debug',+}

        // Lazy commands do not automatically start when Solo starts.
        'Queue' => Command::from('php artisan queue:work')->lazy(),
        'Dumps' => Command::from('php artisan solo:dumps')->lazy(),
        'Reverb' => Command::from('php artisan reverb:start --debug')->lazy(),
        'Pint' => Command::from('./vendor/bin/pint --ansi')->lazy(),
        'Tests' => Command::from('php artisan test --colors=always')->withEnv(['APP_ENV' => 'testing'])->lazy(),
    ],

    // ...
];
```

</x-fenced-code>

That's it!

## Broadcasting Turbo Streams

We'll start by broadcasting new Chirps to all users visiting the `chirps.index` page. To start, we'll register the private broadcasting channel named "_chirps_" in our `routes/channels.php` file. This way, only authenticated users will be able to receive broadcasts:

<x-fenced-code file="routes/channels.php">

```php
<?php

use App\Models\Chirp;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

{+Broadcast::channel('chirps', function ($user) {
    return $user?->exists;
});+}
```

</x-fenced-code>

To start listening for Turbo Broadcasts all we need to do is use the `<x-turbo::stream-from>` Blade component in the page where we want to receive them from. In our case, that will be the `chirps/index.blade.php` view:

<x-fenced-code file="resources/views/chirps/index.blade.php">

```blade
<x-layouts.app :title="__('Chirps')" class="hotwire-native:p-0">
    <section class="w-full md:max-w-lg mx-auto">
        {+<x-turbo::stream-from source="chirps" />+}

        <div class="flex items-center space-x-2 justify-between hotwire-native:hidden">
            <x-text.heading size="xl">{{ __('Chirps') }}</x-text.heading>

            <a href="{{ route('chirps.create') }}" class="btn btn-primary btn-sm invisible [body:where(:has(#create\_chirp:empty))_&]:visible">{{ __('Write') }}</a>
        </div>

        <x-turbo::frame id="create_chirp" class="hidden md:block *:mt-4" loading="lazy" src="{{ route('chirps.create') }}"></x-turbo::frame>

        <div id="chirps" class="has-[*]:mt-6 peer hotwire-native:mt-0 card hotwire-native:rounded-none hotwire-native:mb-20 bg-base-100 divide-y divide-base-200 shadow">
            @each('chirps.partials.chirp', $chirps, 'chirp')
        </div>

        <div class="block space-y-4 peer-has-[*]:hidden my-10 hotwire-native:my-20">
            <x-icons.sparkles size="size-8 hotwire-native:size-12" class="mx-auto text-yellow-500" />
            <p class="text-sm text-base-content/50 text-center">{{ __('The birds are quiet. No chirps yet.') }}</p>
        </div>
    </section>
</x-layouts.app>
```

</x-fenced-code>

Now, when the user visits that page, this component will automatically start listening to a `chirps` _private_ channel for broadcasts. By default, it assumes we're using private channels, but you may configure it to listen to `presence` or `public` channels by passing the `type` prop to the component. In this case, we're passing a string for the channel name, but we could also pass an Eloquent model instance and it would figure out the channel name based on [Laravel's conventions](https://laravel.com/docs/broadcasting#model-broadcasting-conventions).

Now, we're ready to start broadcasting! First, let's add the `Broadcasts` trait to our `Chirp` model:

<x-fenced-code file="app/Models/Chirp.php">

```php
<?php

namespace App\Models;

{+use HotwiredLaravel\TurboLaravel\Models\Broadcasts;+}
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chirp extends Model
{
    {+use Broadcasts;+}
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
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
                turbo_stream()->update('create_chirp', view('chirps.partials.form')),
                turbo_stream()->notice(__('Chirp created.')),
            ]);
        }

        return to_route('chirps.index')->with('notice', __('Chirp created.'));
    }

    // ...
}
```

</x-fenced-code>

To test this, try visiting the `/chirps` page from two different browser windows and creating a Chirp in one of them. The other window should automatically update! We're also broadcasting on-the-fly in the same request/response life-cycle, which could slow down our response time a bit, depending on your load and your queue driver response time. We can delay the broadcasting (which includes view rendering) to a queued job by chaining the `->later()` method, for example.

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

        return to_route('chirps.index')->with('notice', __('Chirp updated.'));
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

        return to_route('chirps.index')->with('notice', __('Chirp deleted.'));
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chirp extends Model
{
    use Broadcasts;
    use HasFactory;

    protected $guarded = [];

    {+protected $broadcasts = [
        'insertsBy' => 'prepend',
    ];+}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    {+public function broadcastsTo()
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
                turbo_stream()->update('create_chirp', view('chirps.partials.form')),
                turbo_stream()->notice(__('Chirp created.')),
            ]);
        }

        return to_route('chirps.index')->with('notice', __('Chirp created.'));
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

        return to_route('chirps.index')->with('notice', __('Chirp updated.'));
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

        return to_route('chirps.index')->with('notice', __('Chirp deleted.'));
    }
}
```

</x-fenced-code>

<x-note type="warning">

<x-slot name="heading">
#### You don't need a model to use Turbo Streams
</x-slot>

We're only covering Turbo Stream broadcasts from an Eloquent model's perspective. However, you may broadcast anything using the `TurboStream` Facade or by chaining the `broadcastTo()` method call when using the `turbo_stream()` response builder function. Check the [Broadcasting docs](/docs/broadcasting#content-handmade-broadcasts) to know more about this.
</x-note>

## Testing it out

Before testing it out, we'll need to start a queue worker. That's because Laravel 11 sets the `QUEUE_CONNECTION=database` by default instead of `sync`, and Turbo Laravel will send automatic broadcasts in background. Let's do that:

```bash
php artisan queue:work --tries=1
```

Also, make sure you have the `APP_URL` correctly set to your local testing URL in your `.env` file, since URLs will be generated in background:

```bash
APP_URL=http://localhost:8000
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
#   updated_at: "2025-4-26 23:01:00",
#   created_at: "2025-4-26 23:01:00",
#   id: 18,
# }
```

![Broadcasting from Tinker](/assets/images/bootcamp/broadcasting-tinker.png?v=6)

### Extra Credit: Fixing The Missing Dropdowns

When creating the Chirp from Tinker, even though we see them appearing on the page, if you look closely, you may notice that the dropdown with the "Edit" and "Delete" buttons is missing. This would also be true if we were using a real queue driver, since it would defer the rendering of the partial to a background queue worker. That's because when we send the broadcasts to run in background, the partial will render without a request and session contexts, so our calls to `Auth::id()` inside of it will always return `null`, which means the dropdown would never render.

Instead of conditionally rendering the dropdown in the server side, let's switch to always rendering them and hide it from our users with a sprinkle of JavaScript instead.

First, let's create the `resources/views/layouts/identity.blade.php` partial to include a few things about the currently authenticated user when there's one:

<x-fenced-code file="resources/views/partials/identity.blade.php" copy>

```blade
@auth
<meta name="current-identity-id" content="{{ Auth::user()->id }}" />
<meta name="current-identity-name" content="{{ Auth::user()->name }}" />
@endauth
```

</x-fenced-code>

Next, update the `head.blade.php` to include it:

<x-fenced-code file="resources/views/partials/head.blade.php">

```blade
<meta charset="utf-8" />
<meta name="viewport"
    content="width=device-width, initial-scale=1.0{{ $scalable ?? false ? ', maximum-scale=1.0, user-scalable=0' : '' }}" />
<meta name="csrf-token" content="{{ csrf_token() }}">

@if ($transitions ?? false)
    <meta name="view-transition" content="same-origin">
@endif

@include('partials.reverb')
{+@include('partials.identity')+}

{{ $meta ?? '' }}

<title>{{ $title ?? config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<link href="{{ tailwindcss('css/app.css') }}" rel="stylesheet" />

<x-importmap::tags />
```

</x-fenced-code>

Now, we're going to create a new Stimulus controller that is going to be responsible for the dropdown visibility. It should only show it if the currently authenticated user is the creator of the Chirp. First, let's create the controller:

```bash
php artisan stimulus:make visible_to_creator
```

Now, update the Stimulus controller to look like this:

<x-fenced-code file="resources/js/controllers/visible_to_creator_controller.js" copy>

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

Now, let's update our `chirp.blade.php` partial to use this controller instead of handling this in the server-side:

<x-fenced-code file="resources/views/chirps/partials/chirp.blade.php">

```blade
<x-turbo::frame :id="$chirp" class="p-6 flex space-x-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-base-content/70 -scale-x-100" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>

    <div class="flex-1">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-base-content/70">{{ $chirp->user->name }}</span>
                <small class="ml-2 text-sm text-base-content/50"><x-local-time-ago :value="$chirp->created_at" /></small>
                @unless ($chirp->created_at->eq($chirp->updated_at))
                <small class="text-sm text-base-content/50"> &middot; edited</small>
                @endunless
            </div>

{-            @if (Auth::id() === $chirp->user->id)
            <x-dropdown class="dropdown-end" class="hidden">-}
{+            <x-dropdown class="dropdown-end" class="hidden" data-controller="visible-to-creator" data-visible-to-creator-id-value="{{ $chirp->user_id }}" data-visible-to-creator-hidden-class="hidden">+}
                <x-slot name="trigger" class="btn-ghost btn-xs">
                    <x-icons.ellipsis-vertical />
                    <span class="sr-only" data-bridge--menu-target="title">{{ __('Options') }}</span>
                </x-slot>

                <x-dropdown.menu class="bg-base-200">
                    <x-dropdown.link href="{{ route('chirps.edit', $chirp) }}" data-bridge-disabled="true" class="hotwire-native:hidden">{{ __('Edit') }}</x-dropdown.link>
                    <x-dropdown.link href="{{ route('chirps.edit', $chirp) }}" data-turbo-frame="_top" class="hidden hotwire-native:inline-block">{{ __('Edit') }}</x-dropdown.link>

                    <form action="{{ route('chirps.destroy', $chirp) }}" method="POST">
                        @method('DELETE')
                        <x-dropdown.button type="submit" data-bridge-disabled="true" class="hotwire-native:hidden">{{ __('Delete') }}</x-dropdown.button>
                        <x-dropdown.button type="submit" data-turbo-frame="_top" class="hidden hotwire-native:inline-block">{{ __('Delete') }}</x-dropdown.button>
                    </form>
                </x-dropdown.menu>
            </x-dropdown>
{-            @endif-}
        </div>
        <p class="mt-4 text-lg">{{ $chirp->content }}</p>
    </div>
</x-turbo::frame>
```

</x-fenced-code>

Next, we need to tweak our `dropdown.blade.php` Blade component to accept and merge the `class`, `data-controller`, and `data-action` attributes:

<x-fenced-code file="resources/views/components/dropdown.blade.php">

```blade
{+@props(['dataController' => ''])+}

{-<details {{ $attributes->merge(['class' => 'dropdown', 'data-controller' => 'bridge--menu']) }}>-}
{+<details {{ $attributes->merge(['class' => 'dropdown', 'data-controller' => 'bridge--menu ' . $dataController]) }}>+}
    {-<summary {{ $trigger->attributes->merge(['class' => 'btn m-1']) }}>-}
    {+<summary {{ $trigger->attributes->merge(['class' => 'btn m-1', 'data-action' => 'click->bridge--menu#show']) }}>+}
        {{ $trigger }}
    </summary>

    {{ $slot }}
</details>
```

</x-fenced-code>

Now, if you try creating another user and test this out, you'll see that the dropdown only shows up for the creator of the Chirp!

![Dropdown only shows up for creator](/assets/images/bootcamp/broadcasting-dropdown-fix.png?v=7)

This change also makes our entire `chirps/partials/chirp.blade.php` partial cacheable! We could cache it and only render that when changes are made to the Chirp model using the Chirp's `updated_at` timestamps, for example.

<x-note type="warning">

<x-slot name="heading">
#### Is hiding the links with CSS enough?
</x-slot>

Hiding the links in the frontend _**MUST NOT**_ be your only protection here. Always ensure users are authorized to perform actions in the server side. We're already doing this in our controller using [Laravel's Authorization Policies](https://laravel.com/docs/authorization#introduction).

</x-note>
