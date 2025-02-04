---
extends: _layouts.bootcamp
title: Our First Bridge Component
description: Our First Bridge Component
order: 12
---

# *10.* Our First Bridge Component

Rendering the create chirps form inline right on the homepage isn't the best UX for mobile. Instead, it would be better to display the form as a native modal screen. Let's implement that, but first, let's hide the entire create chirps form on Hotwire Native.

## Hiding the Elements for Hotwire Native only

We could technically prevent the entire section from even rendering on requests made by Hotwire Native clients using the `@unlessturbonative` Blade directives, something like this:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chirps') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <!-- Add this: -->
        @unlessturbonative
        <x-turbo::frame id="create_chirp" src="{{ route('chirps.create') }}">
            <div class="relative flex items-center justify-center py-10 px-4 rounded-lg border border-dotted border-gray-300">
                <a class="text-gray-700" href="{{ route('chirps.create') }}">
                    Add a new Chirp
                    <span class="absolute inset-0"></span>
                </a>
            </div>
        </x-turbo::frame>
        @endunlessturbonative

        <div id="chirps" class="mt-6 bg-white shadow-sm rounded-lg divide-y">
            @each('chirps._chirp', $chirps, 'chirp')
        </div>
    </div>
</x-app-layout>
```

This would work, but that makes this page harder to cache. Not a problem now, but I prefer doing things like that on the client-side with CSS and a bit of JS.

Since we have configured our WebView to use a custom `User-Agent` header, we can actually detect when our webapp is running inside a Hotwire Native client by checking that. Let's first add a helper to check the platform by creating a `resources/js/helpers/platform.js` file with the following contents:

```js
const { userAgent } = window.navigator;

export const isIos = /iPhone|iPad/.test(userAgent)
export const isAndroid = /Android/.test(userAgent)
export const isMobile = isIos || isAndroid

export const isIosApp = /Hotwire Native iOS/.test(userAgent)
export const isAndroidApp = /Hotwire Native Android/.test(userAgent)
export const isMobileApp = isIosApp || isAndroidApp
```

Now, let's update our `resources/js/app.js` file to add a `turbo-native` class to out HTML document:

```js
import './bootstrap';
import './elements/turbo-echo-stream-tag';
import './libs';
import '@github/time-elements';

// Add this:
import { isMobileApp } from './helpers/platform';

if (isMobileApp) {
    document.documentElement.classList.add('turbo-native');
}
```

This will ensure that when our web app runs inside a Hotwire Native client, a `.turbo-native` class will be added to the `<html>` element in our page, but we're not doing anything yet with it. Let's create a custom Tailwind CSS modifier that will allow us to make things behave differently when the `.turbo-native` class is present in the document.

To do that, open the `tailwind.config.js` file in the root of your Laravel app and make the following changes:

```js
const defaultTheme = require('tailwindcss/defaultTheme');
const plugin = require('tailwindcss/plugin'); // Add this

/** @type {import('tailwindcss').Config} */
module.exports = {
    // ...

    plugins: [
        require("@tailwindcss/forms"),
        // Add this:
        plugin(function ({ addVariant }) {
            return addVariant('turbo-native', ['&.turbo-native', '.turbo-native &']);
        }),
    ],
};
```

With that, we can use the new modifier like any other default modifier in Tailwind. Let's use it to hide the create chirps form on the index page for Hotwire Native clients. Open the `resources/views/chirps/index.blade.php` and make the following changes:

```blade
<x-app-layout>
    <!-- ... -->

    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <!-- Update the Turbo Frame: -->
        <x-turbo::frame id="create_chirp" src="{{ route('chirps.create') }}" class="turbo-native:hidden" loading="lazy">
            <div class="relative flex items-center justify-center py-10 px-4 rounded-lg border border-dotted border-gray-300">
                <a class="text-gray-700" href="{{ route('chirps.create') }}">
                    Add a new Chirp
                    <span class="absolute inset-0"></span>
                </a>
            </div>
        </x-turbo::frame>

        <div id="chirps" class="mt-6 bg-white shadow-sm rounded-lg divide-y">
            @each('chirps._chirp', $chirps, 'chirp')
        </div>
    </div>
</x-app-layout>
```

![Hiding the Create Chirps form](/assets/images/bootcamp/native/fab-chirps-hide-form.png)

Let's also tweak our index page a bit to remove some padding and unnecessary margins:

```blade
<x-app-layout>
    <!-- ... -->

    <!-- Update this element: -->
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 turbo-native:p-0">
        <x-turbo::frame id="create_chirp" src="{{ route('chirps.create') }}" class="turbo-native:hidden" loading="lazy">
            <!-- ... -->
        </x-turbo::frame>

        <!-- Update this element: -->
        <div id="chirps" class="mt-6 bg-white shadow-sm rounded-lg divide-y turbo-native:mt-0">
            @each('chirps._chirp', $chirps, 'chirp')
        </div>
    </div>
</x-app-layout>
```

Let's also hide the web nav bar for Hotwire Native users, our navigation should be fully native on the mobile clients anyways. To do that, change the `resources/views/layouts/navigation.blade.php` file:

```blade
<!-- Update the root nav element: -->
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 turbo-native:hidden">
    <!-- ... -->
</nav>
```

Let's also hide the header section in the `resources/views/layouts/app.blade.php` layout file:

```blade
<!-- Update the Header element: -->
<header class="bg-white shadow turbo-native:hidden">
    <!-- ... -->
</header>
```

And our page should look like this:

![Hotwire Native UI Tweaks](/assets/images/bootcamp/native/fab-ui-tweaks.png)

## Adding the Floating Action Button

Now that the create chirps form is hidden, we need to allow our users to somehow navigate to the create chirps form. Let's create our custom `ChirpsHomeFragment` that will be specific to the `chirps.index` route.

Create a new Kotlin class inside the `features.web` package and call it `ChirpsHomeFragment`:

```kotlin
package com.example.turbochirpernative.features.web

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import com.example.turbochirpernative.R
import dev.hotwire.turbo.nav.TurboNavGraphDestination

@TurboNavGraphDestination(uri = "turbo://fragment/chirps/index")
class ChirpsHomeFragment: WebFragment() {
    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_chirps_home, container, false)
    }
}
```

Notice that we're extending the `WebFragment` class so we need to make it open:

```kotlin
package com.example.turbochirpernative.features.web

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import com.example.turbochirpernative.R
import dev.hotwire.turbo.fragments.TurboWebFragment
import dev.hotwire.turbo.nav.TurboNavGraphDestination

@TurboNavGraphDestination(uri = "turbo://fragment/web")
open class WebFragment: TurboWebFragment() {

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        return inflater.inflate(R.layout.fragment_web, container, false)
    }

    override fun onVisitCompleted(location: String, completedOffline: Boolean) {
        super.onVisitCompleted(location, completedOffline)

        val script = "window.NativeBridge.start();"
        session.webView.evaluateJavascript(script, null)
    }
}
```

Also, we're rendering a different layout file, so we need to create it. Add a new layout file by right-clicking on the `res/layout` folder and choosing the "New -> Layout Resource File" option in the menu, add the following contents to it:

```xml
<?xml version="1.0" encoding="utf-8"?>
<androidx.constraintlayout.widget.ConstraintLayout
    xmlns:android="http://schemas.android.com/apk/res/android"
    xmlns:app="http://schemas.android.com/apk/res-auto"
    xmlns:tools="http://schemas.android.com/tools"
    android:layout_width="match_parent"
    android:layout_height="match_parent">

    <com.google.android.material.appbar.AppBarLayout
        android:id="@+id/app_bar"
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:background="?colorPrimary"
        app:layout_constraintEnd_toEndOf="parent"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintTop_toTopOf="parent">

        <com.google.android.material.appbar.MaterialToolbar
            android:id="@+id/toolbar"
            android:layout_width="match_parent"
            app:titleTextColor="?colorOnPrimary"
            android:layout_height="wrap_content" />

    </com.google.android.material.appbar.AppBarLayout>

    <include
        layout="@layout/turbo_view"
        android:layout_width="match_parent"
        android:layout_height="0dp"
        app:layout_constraintBottom_toBottomOf="parent"
        app:layout_constraintTop_toBottomOf="@+id/app_bar" />

    <com.google.android.material.floatingactionbutton.FloatingActionButton
        android:id="@+id/floatingActionButton"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:layout_margin="16dp"
        android:clickable="true"
        android:focusable="true"
        android:tint="@color/white"
        app:tint="@color/white"
        app:backgroundTint="@color/indigo_500"
        app:layout_constraintBottom_toBottomOf="parent"
        app:layout_constraintEnd_toEndOf="parent"
        app:srcCompat="@android:drawable/ic_input_add"
        android:contentDescription="New Chirp"
    />

</androidx.constraintlayout.widget.ConstraintLayout>
```

Now, let's register our new fragment in the `MainSessionNavHostFragment`:

```kotlin

// ...

class MainSessionNavHostFragment : TurboSessionNavHostFragment() {
    override val registeredFragments: List<KClass<out Fragment>>
        get() = listOf(
            WebFragment::class,
            LoginFragment::class,
            ChirpsHomeFragment::class, // Add this
        )

    // ...
}
```

Now, let's configure our route path in the `assets/json/configuration.json` file to open that fragment whenever we visit the `chirps.index` route:

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

Notice that the URI matches what we defined in the `ChirpsHomeFragment`: `turbo://fragment/chirps/index`.

At this point, our app looks like this:

![With The FAB](/assets/images/bootcamp/native/fab-showing-up.png)

But our button doesn't work yet. Let's tell Hotwire Native to make a visit to the `chirps/create` route. First, add the new constant to the `util.Constants` file:

```kotlin
package com.example.turbochirpernative.util

const val BASE_URL = "http://10.0.2.2"
const val CHIRPS_HOME_URL = "$BASE_URL/chirps"
const val CHIRPS_CREATE_URL = "$CHIRPS_HOME_URL/create" // Add this

const val API_BASE_URL = "$BASE_URL/api"
const val API_CSRF_COOKIES_URL = "$BASE_URL/sanctum/csrf-cookie"
const val API_LOGIN_URL = "$API_BASE_URL/login"
```

Now, change the `ChirpsHomeFragment` to setup the click handler on that fab:

```kotlin
@TurboNavGraphDestination(uri = "turbo://fragment/chirps/index")
class ChirpsHomeFragment: WebFragment() {
    // ...

    // Add this:
    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        setupFab()
    }

    // Add this:
    private fun setupFab() {
        view?.findViewById<FloatingActionButton>(R.id.createChirpsFab)?.setOnClickListener {
            navigate(CHIRPS_CREATE_URL)
        }
    }
}
```

Now, let's click on it and you should be redirected to the create chirps form! How cool is that?!

![Create Chirps Form](/assets/images/bootcamp/native/fab-create-chirps-form-in-new-page.png)

If you try creating a chirp, however, you should see some interesting behavior...

![Wrong behavior after creating chirps](/assets/images/bootcamp/native/fab-wrong-behavior-after-creating.png)

That's not good. That's because we're returning Turbo Streams on the `ChirpController@store` action. Let's change it so it doesn't do that when the request was done via a Hotwire Native client. We want the redirect there. Head to the `app/Http/Controllers/ChirpController.php` file and change the `store` action like so:

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

        // Check if the request came from a Hotwire Native client...
        if ($request->wantsTurboStream() && ! $request->wasFromHotwireNative()) {
            return turbo_stream([
                turbo_stream($chirp, 'prepend'),
                turbo_stream()->update('create_chirp', view('chirps._form')),
                turbo_stream()->append('notifications', view('layouts.notification', [
                    'message' => __('Chirp created.'),
                ]))
            ]);
        }

        return redirect()
            ->route('chirps.index')
            ->with('status', __('Chirp created.'));
    }

    // ...
}
```

This should redirect us to the home page after creating a chirp and the new Chirp should appear there! Cool.

We have lost the flash message, but we'll handle that soon. One thing is bothering me: we're showing the "Turbo Chirper Native" title on every screen. I don't like that. Instead, I want each screen to customize the title. Well, it turns out it already does that based on the title of the page we're visiting. Let's change our `app.blade.php` layout file to accept a `$title` prop:

```blade
<title>{{ $title ?? config('app.name', 'Laravel') }}</title>
```

Now, let's register the prop in the `AppLayout` component in `app/View/Components`:

```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    public function __construct(public ?string $title = null)
    {
    }

    public function render()
    {
        return view('layouts.app');
    }
}
```

Then, in the `resources/views/chirps/create.blade.php` file, set the `:title` prop in the `<x-app-layout>` component:

```blade
<!-- Add the title prop: -->
<x-app-layout :title="__('Create Chirp')">
    <!-- ... -->
</x-app-layout>
```

And with that, our create chirps page should have the "Create Chirp" title:

![Create Chirp Screen with Title](/assets/images/bootcamp/native/fab-create-chirps-with-title.png)
