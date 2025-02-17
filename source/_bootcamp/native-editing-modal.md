---
extends: _layouts.bootcamp
title: Editing in a Modal
description: Editing in a Native Modal
order: 13
---

# *11.* Editing in a Modal

Right now our editing flow is not very mobile-friendly. Instead of showing the edit form inline in the list, we could show it as a native modal screen instead. But before we introduce the new native screen, let's first ensure our web dropdown appears as a real native bottom sheet list of options. That will serve as an example of how we can bridge the web and mobile native Worlds with a little bit of JavaScript. This approach was based on how the Hey app works (at least on the pieces I could spot from inspecting the page source).

## Dropdowns in a BottomSheet modal

Let's first create our BottomSheet. This one won't be driven by a navigation, though. We're going implement a web->native bridge that we can trigger when the app is running inside a Hotwire Native client. For now, let's create a new XML view. Head to the sidebar, under "res/layout", right-click on it an choose "New -> Layout Resource File". Call it "popup_menu.xml" and add this content:

```xml
<?xml version="1.0" encoding="utf-8"?>
<androidx.cardview.widget.CardView
    xmlns:android="http://schemas.android.com/apk/res/android"
    android:layout_width="match_parent"
    android:layout_height="wrap_content">

    <LinearLayout
        android:id="@+id/popupMenuWrapper"
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:layout_margin="2dp"
        android:orientation="vertical">
    </LinearLayout>

</androidx.cardview.widget.CardView>
```

This will be programatically added, so what's really important here is the `android:id` property. Since we're going to trigger this native feature from the web, the Hotwire Native for Android [documentation says](https://github.com/hotwired/turbo-android/blob/main/docs/ADVANCED-OPTIONS.md#native---javascript-integration) that the best location is in the `TurboSessionNavHostFragment::onSessionCreated` function. Let's then tweak our `MainSessionNavHostFragment` with the following change:

```kotlin
package com.example.turbochirpernative.main

import android.webkit.JavascriptInterface // Add this
import android.webkit.WebView
import android.widget.Button // Add this
import android.widget.LinearLayout
import android.widget.Toast // Add this
import androidx.appcompat.app.AppCompatActivity
import androidx.compose.ui.res.integerResource
import androidx.fragment.app.Fragment
import com.example.turbochirpernative.BuildConfig
import com.example.turbochirpernative.R // Add this
import com.example.turbochirpernative.features.auth.LoginFragment
import com.example.turbochirpernative.features.web.ChirpsHomeFragment
import com.example.turbochirpernative.features.web.WebFragment
import com.example.turbochirpernative.util.CHIRPS_HOME_URL
import com.google.android.material.bottomsheet.BottomSheetDialog // Add this
import com.google.gson.Gson // Add this
import dev.hotwire.turbo.config.TurboPathConfiguration
import dev.hotwire.turbo.session.TurboSessionNavHostFragment
import kotlin.reflect.KClass

class MainSessionNavHostFragment : TurboSessionNavHostFragment(), PopupMenuDelegator {
    // ...

    override fun onSessionCreated() {
        super.onSessionCreated()
        session.webView.settings.userAgentString = customUserAgent(session.webView)

        if (BuildConfig.DEBUG) {
            session.setDebugLoggingEnabled(true)
            WebView.setWebContentsDebuggingEnabled(true)
        }

        // Add this:
        session.webView.addJavascriptInterface(
            JsBridge(this),
            "NativeBridge",
        )
    }

    private fun customUserAgent(webView: WebView): String {
        return "Hotwire Native Android ${webView.settings.userAgentString}"
    }

    override fun showPopupMenu(options: PopupMenu) {
        activity?.runOnUiThread {
            val context = requireContext()

            val dialog = BottomSheetDialog(context)

            val view = layoutInflater.inflate(R.layout.popup_menu, null)!!

            val menuWrapper = view.findViewById<LinearLayout>(R.id.popupMenuWrapper)!!

            menuWrapper.removeAllViews()

            options.items.forEach { item ->
                val button = Button(context)

                button.text = item.text
                button.setBackgroundColor(resources.getColor(R.color.white, null))

                button.setOnClickListener {
                    session.webView.evaluateJavascript(
                        "window.dispatchEvent(new CustomEvent('popup-menu:selected', { detail: { index: " + item.index + ", text: '" + item.text + "' } }))",
                        null
                    )

                    dialog.dismiss()
                }

                menuWrapper.addView(button)
            }

            dialog.setOnCancelListener {
                session.webView.evaluateJavascript("window.dispatchEvent(new CustomEvent('popup-menu:canceled'))", null)
            }

            dialog.setCancelable(true)

            dialog.setContentView(view)

            dialog.show()
        }
    }

    override fun showToast(msg: String) {
        activity?.runOnUiThread {
            Toast
                .makeText(requireContext(), msg, Toast.LENGTH_SHORT)
                .show()
        }
    }
}

data class MenuItem(
    val text: String,
    val index: Int,
)

data class PopupMenu(
    val items: List<MenuItem>,
)

interface PopupMenuDelegator {
    fun showPopupMenu(options: PopupMenu);
    fun showToast(msg: String);
}

class JsBridge(private var delegator: PopupMenuDelegator) {
    @JavascriptInterface
    override fun toString(): String {
        return "NativeBridge"
    }

    @JavascriptInterface
    fun showPopup(json: String) {
        val gson = Gson()

        val options = gson.fromJson(json, PopupMenu::class.java)

        delegator.showPopupMenu(options)
    }

    @JavascriptInterface
    fun showToast(msg: String) {
        delegator.showToast(msg)
    }
}
```

Now we have the BottomSheet Menu ready to be triggered by our webapp.

## Telling the Native App to Show The Options

We're gonna create a Stimulus controller that will act when a user triggers the dropdown inside a Hotwire Native client. We're also gonna use the custom User Agent to detect we're on that platform. Whenever that is the case, we'll get some metadata from the HTML and pass that to the Native app so it can build the native menu. When the user either picks one of the options or dismisses the menu, we're gonna notify the web app about it.

Let's then add the Stimulus controller. Open a terminal at the root of the webapp and run:

```bash
php artisan stimulus:make bridge/popup_menu_controller
```

That should take care of creating and registering the controller for us. Open that Stimulus controller and place the following content:

```js
import { Controller } from "@hotwired/stimulus"
import { isMobileApp } from "../../helpers/platform"
import { BridgeElement } from "../../helpers/bridge_element"

// Connects to data-controller="bridge--popup-menu"
export default class extends Controller {
    static targets = ['option']

    connect() {
        this.clearCallbacks()
    }

    update(event) {
        if (! this.enabled) return

        event.stopImmediatePropagation()
        event.preventDefault()

        this.notifyBridgeToDisplayMenu(event)
    }

    notifyBridgeToDisplayMenu(event) {
        const items = BridgeElement.makeMenuItems(this.optionTargets)

        this.send(items, item => {
            new BridgeElement(this.optionTargets[item.index]).click()
        })
    }

    send(items, callback) {
        this.registeredCallbacks.push(callback)
        window.NativeBridge.showPopup(JSON.stringify({ items }))
    }

    handle(event) {
        let handler = this.registeredCallbacks.pop()

        if (! handler) return

        handler.call(this, event.detail)
    }

    clearCallbacks() {
        this.registeredCallbacks = []
    }

    get enabled() {
        return isMobileApp
    }
}
```

So, we're detecting if we're on a Hotwire Native client using the `isMobileApp` platform check (that same one we're using to add the `turbo-native` CSS class to the HTML document). The `update` method will be triggered by the dropdown trigger (same one that opens it), but we need to register it *before* the normal web trigger because we want to stop it from showing the dropdown and, instead, show the native menu. When the dropdown opens, we'll scan through all the option targets and fetch metadata from it, then register a callback in the controller's instance. When the user picks one of the options, the native client will dispatch a custom event to the window, so all instances of dropdown controllers will receive that event, but only the one with the callback will act on it. Then, it should trigger the default behavior of that option (link or button click).

When the controller scans the option targets, it builds an instance of a `BridgeElement` class, which we don't have yet. Let's add one at `resources/js/helpers/bridge_element.js`:

```js
import { isAndroidApp, isMobileApp } from "./platform"

export class BridgeElement {
    static makeMenuItems(elements){
        return elements.map((element, index) => {
            return new BridgeElement(element).asMenuItem(index)
        }).filter(item => item)
    }

    constructor(element) {
        this.element = element
    }

    asMenuItem(index) {
        if (this.disabled) return

        return {
            text: this.text,
            index: index,
        }
    }

    click() {
        this.ensureFrameOrFormTargetsTop()

        // Remove the target attribute before clicking to avoid an
        // issue in Android WebView that prevents a target="_blank"
        // URL from being obtained from a JavaScript click.

        if (isAndroidApp) {
            this.element.removeAttribute("target")
        }

        this.element.click()
    }

    ensureFrameOrFormTargetsTop() {
        this.ensureFrameTargetsTop()
        this.ensureFormTargetsTop()
    }

    ensureFrameTargetsTop() {
        let frame = this.element.closest('turbo-frame')

        if (! frame) return

        frame.setAttribute('target', '_top')
    }

    ensureFormTargetsTop() {
        let form = this.element.closest('form')

        if (! form) return

        form.setAttribute('data-turbo-frame', '_top')
    }

    get text() {
        return this.element.textContent.trim()
    }

    get disabled() {
        return ! this.enabled
    }

    get enabled() {
        return isMobileApp
    }
}
```

Next, let's update the our `dropdown` blade component to use this Stimulus controller we just created:

```blade
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

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

<!-- Update the data-controller attribute: -->

<div
    class="relative"
    data-controller="bridge--popup-menu dropdown"
    data-action="
        click@window->dropdown#close
        turbo:load@window->dropdown#close
        popup-menu:canceled@window->bridge--popup-menu#clearCallbacks
        popup-menu:selected@window->bridge--popup-menu#handle
    "
    {{ $attributes }}
>
    <!-- Update the data-action attribute: -->
    <div data-action="click->bridge--popup-menu#update click->dropdown#toggle click->dropdown#stop">
        {{ $trigger }}
    </div>

    <div
        data-dropdown-target="content"
        data-transition-enter="transition ease-out duration-200"
        data-transition-enter-start="transform opacity-0 scale-95"
        data-transition-enter-end="transform opacity-100 scale-100"
        data-transition-leave="transition ease-in duration-75"
        data-transition-leave-start="transform opacity-100 scale-100"
        data-transition-leave-end="transform opacity-0 scale-95"
        class="hidden absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
```

Now, we need to make sure the options are registered in the Stimulus controller where this Blade component is used. Let's update our `_chirp` blade partial:

```blade
<x-turbo::frame :id="$chirp" class="block p-6">
    <div class="flex space-x-2">
        <!-- ... -->

        <div class="flex-1">
            <div class="flex justify-between items-center">
                <!-- ... -->

                @if (Auth::id() === $chirp->user->id)
                <x-dropdown align="right" width="48" data-bridge--popup-menu-msg-value="{{ $chirp->message }}">
                    <!-- ... -->

                    <x-slot name="content">
                        <!-- Update the link: -->
                        <a href="{{ route('chirps.edit', $chirp) }}" data-bridge--popup-menu-target="option" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:bg-gray-100 transition duration-150 ease-in-out">
                            Edit
                        </a>

                        <form action="{{ route('chirps.destroy', $chirp) }}" method="POST">
                            @method('DELETE')
                            <!-- Update the button: -->
                            <button data-bridge--popup-menu-target="option" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:bg-gray-100 transition duration-150 ease-in-out">
                                Delete
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
                @endif
            </div>

            <p class="mt-4 text-lg text-gray-900">{{ $chirp->message }}</p>
        </div>
    </div>
</x-turbo::frame>
```

This should get our dropdown appearing as a BottomSheet menu!

![Edit Bottom Sheet Menu](/assets/images/bootcamp/native/edit-bottom-sheet-menu.png)

Our Bridge Popup Menu controller also changes the frame target to `_top`, which is handy in our case as that will make a full page visit to the edit page instead of rendering the form inline!

If you try to update a chirp, however, the behavior will be similar to what it was previously on the create chirp flow. Let's fix it by letting the controller redirect to the list index of chirps instead of returning Turbo Streams. Since we're tinkering with the controller, let's also update the destroy flow so it also redirects there:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

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

        // Check if the request was from a Hotwire Native client:
        if ($request->wantsTurboStream() && ! $request->wasFromHotwireNative()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.notification', [
                    'message' => __('Chirp updated.'),
                ])),
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('status', __('Chirp updated.'));
    }

    // ...

    public function destroy(Request $request, Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        // Check if the request was from a Hotwire Native client:
        if ($request->wantsTurboStream() && ! $request->wasFromHotwireNative()) {
            return turbo_stream([
                turbo_stream($chirp),
                turbo_stream()->append('notifications', view('layouts.notification', [
                    'message' => __('Chirp deleted.'),
                ])),
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('status', __('Chirp deleted.'));
    }
}
```

That should have our edit and delete flows working properly, which is cool!

## Using a Native Modal Screen for Forms

Instead of showing the edit form in a new screen, I think it would be cool to introduce a native modal screen that other pages could use as well. First, add a new Fragment called `WebModalFragment` under the `web` package in the Android project without a layout:

```kotlin
package com.example.turbochirpernative.features.web

import android.os.Bundle
import android.view.View
import com.example.turbochirpernative.util.displayBackButtonAsCloseIcon
import dev.hotwire.turbo.nav.TurboNavGraphDestination

@TurboNavGraphDestination(uri = "turbo://fragment/web/modal")
class WebModalFragment : WebFragment() {
    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        initToolbar()
    }

    private fun initToolbar() {
        toolbarForNavigation()?.displayBackButtonAsCloseIcon()
    }
}
```

We need to create a new Extensions.kt file inside the `util` package so we can add this `displayBackButtonAsCloseIcon` method to the Toolbar:

```kotlin
package com.example.turbochirpernative.util

import androidx.appcompat.widget.Toolbar
import androidx.core.content.ContextCompat
import com.example.turbochirpernative.R

fun Toolbar.displayBackButtonAsCloseIcon() {
    navigationIcon = ContextCompat.getDrawable(context, R.drawable.ic_close)
}
```

Next, add a new Vector to the drawables by right-clicking on "New -> Vector Asset" inside the "res/drawables" folder, name it `ic_close` and choose the close icon.

Then, let's register the `WebModalFragment` in our `MainSessionNavHostFragment`:

```kotlin
package com.example.turbochirpernative.main

// ...
import com.example.turbochirpernative.features.web.WebModalFragment // Add this

class MainSessionNavHostFragment : TurboSessionNavHostFragment(), PopupMenuDelegator {
    // ...

    override val registeredFragments: List<KClass<out Fragment>>
        get() = listOf(
            WebFragment::class,
            WebModalFragment::class, // Add this
            LoginFragment::class,
            ChirpsHomeFragment::class,
        )

    // ...
}

// ...
```

We need to add a new entry to our configuration so any URI ending with `/edit` or `/create` (our forms), will open inside of a web modal fragment instead of the default web fragment:

```json
{
  "settings": {
    "screenshots_enabled": true
  },
  "rules": [
    {
      "patterns": [
        ".*"
      ],
      "properties": {
        "context": "default",
        "uri": "turbo://fragment/web",
        "pull_to_refresh_enabled": true
      }
    },
    {
      "patterns": [
        "/edit$",
        "/edit/$",
        "/create$",
        "/create/$"
      ],
      "properties": {
        "context": "default",
        "uri": "turbo://fragment/web/modal",
        "pull_to_refresh_enabled": false
      }
    },
    {
      "patterns": [
        "login$",
        "login/$"
      ],
      "properties": {
        "context": "default",
        "uri": "turbo://fragment/auth/login",
        "pull_to_refresh_enabled": false
      }
    },
    {
      "patterns": [
        "chirps$",
        "chirps/$"
      ],
      "properties": {
        "context": "default",
        "uri": "turbo://fragment/chirps/index",
        "pull_to_refresh_enabled": true
      }
    }
  ]
}
```

Let's also hide the cancel button when inside a Hotwire Native client by updating our `resources/chirps/_form.blade.php` in our web app:

```blade
<form action="{{ ($chirp ?? false) ? route('chirps.update', $chirp) : route('chirps.store') }}" method="POST">
    <!-- ... -->

    <div class="flex items-center justify-start space-x-2">
        <x-primary-button class="mt-4">
            {{ __('Chirp') }}
        </x-primary-button>

        @if ($chirp ?? false)
        <!-- Update the link: -->
        <a href="{{ route('chirps.index') }}" class="mt-4 turbo-native:hidden">Cancel</a>
        @endif
    </div>
</form>
```

## Testing It Out

Now, our app should be a bit nicer:

![Edit Chirp as modal](/assets/images/bootcamp/native/web-modal-fragment.png)
