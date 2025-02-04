---
extends: _layouts.bootcamp
title: Native Toasts
description: Native Toasts
order: 15
---

# *13.* Native Toasts

You may have noticed that our flash messages are not behaving correctly in the native side. That's because of how our native client handles redirects. It looks like it actiaves the cache first, which triggers a new request to the web app, then it visits the redirected URL, which then triggers another visit. Our flash messages are flashed for a single subsequent request, which is the one that comes from the activated cache, so they get lost when the redirect visit starts.

We can fix that by using some predefined URLs in the webapp that will only happen for Hotwire Native requests and they only instruct the web app to either recede, resume, or refresh the screens natively instead of following redirects.

Turbo Laravel ships with those predefined URLs for you:

```bash
php artisan route:list | grep turbo
  GET|HEAD        recede_historical_location turbo_recede_historical_location…
  GET|HEAD        refresh_historical_location turbo_refresh_historical_locati…
  GET|HEAD        resume_historical_location turbo_resume_historical_location…
```

If you make a request to those routes, you will see that they don't actually have any contents on them:

```bash
curl localhost/recede_historical_location
Going back...

curl localhost/refresh_historical_location
Refreshing...

curl localhost/resume_historical_location
Staying put...
```

Turbo Laravel ships with a `InteractsWithTurboNativeNavigation` trait that we can use in our controllers to redirect to these routes when the request comes from a Hotwire Native client or to a fallback route otherwise.

Let's change our controller to make use of that. We're going to change both the store and update actions to _recede_ the screen stacks instead of a regular redirect:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
use HotwiredLaravel\TurboLaravel\Http\Controllers\Concerns\InteractsWithHotwireNativeNavigation; // Add this

class ChirpController extends Controller
{
    // Add this:
    use InteractsWithHotwireNativeNavigation;

    // ...

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp = $request->user()->chirps()->create($validated);

        // Check if the request came from a Hotwire Native client...
        if ($request->wantsTurboStream() && ! $request->wasFromHotwireNative()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps._form')),
                turbo_stream()->append('notifications', view('layouts.notification', [
                    'message' => __('Chirp created.'),
                ])),
            ]);
        }

        // Use the recedeOrRedirectTo helper:
        return $this->recedeOrRedirectTo(route('chirps.index'))
            ->with('status', __('Chirp created.'));
    }

    // ...
 
    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $chirp->update($validated);

        // Check if the request was from a Hotwire Native client...
        if ($request->wantsTurboStream() && ! $request->wasFromHotwireNative()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.notification', [
                    'message' => __('Chirp updated.'),
                ])),
            ]);
        }

        // Use the recedeOrRedirectTo helper:
        return $this->recedeOrRedirectTo(route('chirps.index'))
            ->with('status', __('Chirp updated.'));
    }

    // ...
}
```

The `recedeOrRedirectTo` method is the one that does the job of checking if the request comes from a Hotwire Native client or a regular web client and recides to redirect to the recede route instead of default redirect route. We also need to ensure that for when we're creating or updating chirps, we only return Turbo Streams if the request didn't come from a Hotwire Native client.

Next, we need to configure our specific routes in the `Constants.kt` file:

```kotlin
package com.example.turbochirpernative.util

const val BASE_URL = "http://10.0.2.2"
const val CHIRPS_HOME_URL = "$BASE_URL/chirps"
const val CHIRPS_CREATE_URL = "$BASE_URL/chirps/create"

// Native Redirect Routes
const val REFRESH_HISTORICAL_URL = "$BASE_URL/refresh_historical_location"
const val RECEDE_HISTORICAL_URL = "$BASE_URL/recede_historical_location"
const val RESUME_HISTORICAL_URL = "$BASE_URL/resume_historical_location"

const val API_BASE_URL = "$BASE_URL/api"
const val API_CSRF_COOKIES_URL = "$BASE_URL/sanctum/csrf-cookie"
const val API_LOGIN_URL = "$API_BASE_URL/login"
```

Now, we need to update our main `WebFragment` (which is inherited by our sub fragments like the modal one) to catch redirects to these specific routes and stop the navigation and instead apply their native behavior in the navigation stack:

```kotlin
package com.example.turbochirpernative.features.web

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import com.example.turbochirpernative.R

// Add these imports:
import com.example.turbochirpernative.util.RECEDE_HISTORICAL_URL
import com.example.turbochirpernative.util.REFRESH_HISTORICAL_URL
import com.example.turbochirpernative.util.RESUME_HISTORICAL_URL

import dev.hotwire.turbo.fragments.TurboWebFragment
import dev.hotwire.turbo.nav.TurboNavGraphDestination

@TurboNavGraphDestination(uri = "turbo://fragment/web")
open class WebFragment: TurboWebFragment() {
    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_web, container, false)
    }

    // Add this:
    override fun shouldNavigateTo(newLocation: String): Boolean {
        return when (newLocation) {
            RECEDE_HISTORICAL_URL -> {
                navigateBack()
                false
            }
            REFRESH_HISTORICAL_URL -> {
                refresh()
                false
            }
            RESUME_HISTORICAL_URL -> {
                false
            }
            else -> super.shouldNavigateTo(newLocation)
        }
    }
}
```

Now, if we try creating or updating a _Chirp_, we should see the web flash messages appearing!

![Web Flash Messages on Native](/assets/images/bootcamp/native/flash-messages-web-on-native.png)

## Flash as Toast Messages

It's cool that we're showing the web flash messages, but it would be even better if we could instead convert those messages to appear as real native Toasts, don't you think?! So, let's implement that.

First, we'll need to hide the flash messages wrapper, so they don't appear in a Hotwire Native context. We can do that using the `turbo-native:` Tailwind variant we already have. Open the `resources/views/layouts/notifications.blade.php` file and update its contents:

```blade
<!-- Update the root element: -->
<div id="notifications" class="fixed top-10 left-0 w-full text-center flex flex-col items-center space-y-2 justify-center z-10 opacity-80 turbo-native:hidden">
    @if ($message = session()->get('status', null))
        @include('layouts.notification', ['message' => $message])
    @endif
</div>
```

With that, the flash messages shouldn't appear in Hotwire Native clients, but they should still appear in the web ones.

Next, let's create a bridge Stimulus controller that will send a message to the Native client to show convert those web flash messages into native ones.

```bash
php artisan stimulus:make bridge/toast_controller
```

We'll update the notification view (the singular one) and make it use the controller we just created. It's important to note that the bridge controller needs to be listed before all other controllers:

```blade
<div data-turbo-cache="false" class="py-1 px-4 leading-7 text-center text-white rounded-full bg-gray-900 transition-all animate-appear-then-fade" data-controller="bridge--toast notification" data-action="animationend->notification#remove">
    {{ $message }}
</div>
```

Now, let's implement our Stimulus Toast controller:

```js
import { Controller } from "@hotwired/stimulus"
import { isMobileApp } from "../../helpers/platform"

// Connects to data-controller="bridge--toast"
export default class extends Controller {
    connect() {
        if (! this.enabled) return

        window.NativeBridge.showToast(this.element.textContent.trim())
        this.element.remove();
    }

    get enabled() {
        return isMobileApp
    }
}
```

It will simply read the text content of the flash message and pass it up to the native client to as a toast, then it removes the flash element from the page.

Now, we need to implement the `showToast` handling in our `MainSessionNavHostFragment`:

```kotlin
package com.example.turbochirpernative.main

// ...
import android.widget.Toast // Add this

class MainSessionNavHostFragment : TurboSessionNavHostFragment(), PopupMenuDelegator {
    // ...

    // Add this:
    override fun showToast(msg: String) {
        activity?.runOnUiThread {
            Toast
                .makeText(requireContext(), msg, Toast.LENGTH_SHORT)
                .show()
        }
    }

    // ...
}

interface PopupMenuDelegator {
    fun showPopupMenu(options: PopupMenu);
    fun showToast(msg: String); // Add this
    fun showConfirmationModal(msg: String);
}

class JsBridge(private var delegator: PopupMenuDelegator) {
    // ...

    // Add this:
    @JavascriptInterface
    fun showToast(msg: String) {
        delegator.showToast(msg)
    }

    @JavascriptInterface
    fun showConfirmationModal(msg: String) {
        delegator.showConfirmationModal(msg)
    }
}
```

Now, we should have the native Toast messages!

![Flash Messages as Toast](/assets/images/bootcamp/native/flash-messages-toast.png)

What is cool about this is that we have full control over the text message shown in the notification from our web app! Let's change the message to use an emoji, for instance:

![Flash Messages Deleted With Emoji on Native](/assets/images/bootcamp/native/flash-message-changed-emoji-native.png)
![Flash Messages Deleted With Emoji on Web](/assets/images/bootcamp/native/flash-message-changed-emoji-web.png)

That's it!
