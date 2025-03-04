---
extends: _layouts.bootcamp
title: Creating Chirps
description: Let's allow users to post short messages called Chirps.
order: 3
---

# *02.* Creating Chirps

Let's allow users to post short messages called _Chirps_.

## Models, migrations, and controllers

To allow users to post _Chirps_, we'll use migrations, models, and controllers. Let's briefly cover those concepts:

* [Models](https://laravel.com/docs/eloquent) provide a powerful and enjoyable interface for you to interact with the tables in your database.
* [Migrations](https://laravel.com/docs/migrations) allow you to easily create and modify the tables in your database. They ensure that the same database structure exists everywhere that your application runs.
* [Controllers](https://laravel.com/docs/controllers) are responsible for processing requests made to your application and returning a response.

Almost every feature you build will involve all of these pieces working together in harmony, so the `artisan make:model` command can create them all for you at once.

Let's create a model, migration, and resource controller for our Chirps with the following command:

```bash
php artisan make:model -mcr Chirp
```

You can see all the available options by using the `--help` option, like `php artisan make:model --help`.

This command will create three files:

* `app/Models/Chirp.php` - The Eloquent model.
* `database/migrations/<timestamp>_create_chirps_table.php` - The database migration that will create the database table.
* `app/Http/Controller/ChirpController.php` - The HTTP controller that will take incoming requests and return responses.

## Routing

We will also need to create URLs for our controller. We can do this by adding "routes", which are managed in the `routes` directory of your project. Because we're using a resource controller, we can use a single `Route::resource()` statement to define all of the routes following a conventional URL structure.

To start with, we are going to enable three routes:

* The `index` route will display our listing of Chirps.
* The `create` route will display the form to create Chirps.
* The `store` route will be used for saving new Chirps.

We are also going to place these routes behind two [middlewares](https://laravel.com/docs/middleware):

* The `auth` middleware ensures that only logged-in users can access the route.
* The `verified` middleware will be used if you decide to enable [email verification](https://laravel.com/docs/verification).

<x-fenced-code file="routes/web.php">

```php
{+use App\Http\Controllers\ChirpController; +}
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePasswordController;
use Illuminate\Support\Facades\Route;

// ...

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

{+Route::resource('chirps', ChirpController::class)
    ->only(['index', 'create', 'store'])
    ->middleware(['auth', 'verified']); +}

Route::middleware('auth')->group(function () {
    // ...
});

require __DIR__.'/auth.php';
```

</x-fenced-code>

This will create the following routes:

| Verb | URI | Action | Route Name |
|---|---|---|---|
| GET | `/chirps` | index | `chirps.index` |
| GET | `/chirps/create` | create | `chirps.create` |
| POST | `/chirps` | store | `chirps.store` |

You may view all of the routes for your application by running the `php artisan route:list` command.

Let's test our route and controller by returning a test message from the `index` method of our new `ChirpController` class:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php 
<?php

// ...

class ChirpController extends Controller
{
    public function index()
    {
{-        //-}
{+        return 'Hello, World!';+}
    }

    // ...
}
```

</x-fenced-code>

If you are still logged in from earlier, you should see your message when navigating to [http://localhost:8000/chirps](http://localhost:8000/chirps), or [http://localhost/chirps](http://localhost/chirps) if you're using Sail!

### Adding The Form

Let's update our `index` action in the `ChirpController` to render the view that will display the listing of Chirps and a link to create a Chirp. We'll also update the `create` action to render the view that will display the form to create Chirps:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php
<?php

// ...

class ChirpController extends Controller
{
    public function index()
    {
{-        return 'Hello, World';-}
{+        return view('chirps.index', [
            //
        ]);+}
    }

    public function create()
    {
{-        //-}
{+        return view('chirps.create', [
            //
        ]);+}
    }

    // ...
}
```

</x-fenced-code>

We can then create our `chirps.index` view with a link to our form for creating new Chirps:

<x-fenced-code file="resources/views/chirps/index.blade.php" copy>

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[__('Chirps')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            @include('chirps.partials.new-chirp-trigger')
        </div>
    </div>
</x-app-layout>
```

</x-fenced-code>

This view is including a partial called `new-chirp-trigger`, so create the partial with the following content:

<x-fenced-code file="resources/views/chirps/partials/new-chirp-trigger.blade.php" copy>

```blade 
<div class="relative flex items-center pt-2 pb-8 px-3 rounded-lg transition bg-white border border-gray-300 dark:border-gray-700 dark:bg-gray-900 hover:bg-opacity-70">
    <a class="text-gray-500" href="{{ route('chirps.create') }}">
        {{ __('Create a Chirp') }}
        <span class="absolute inset-0"></span>
    </a>
</div>
```

</x-fenced-code>

Then, let's create our `chirps.create` page view with the Chirps form:

<x-fenced-code file="resources/views/chirps/create.blade.php" copy>

```blade 
<x-app-layout :title="__('Create Chirp')">
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[route('chirps.index') => __('Chirps'), __('New Chirp')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('chirps.partials.form')
        </div>
    </div>
</x-app-layout>
```

</x-fenced-code>

Again, this view is including a `form` partial. Create that file with the following content:

<x-fenced-code file="resources/views/chirps/partials/form.blade.php" copy>

```blade 
<form action="{{ route('chirps.store') }}" method="POST" class="w-full">
    @csrf

    <div>
        <x-input-label for="message" :value="__('Message')" class="sr-only" />
        <x-textarea-input id="message" name="message" placeholder="{{ __('What\'s on your mind?') }}" class="block w-full" />
        <x-input-error :messages="$errors->get('message')" class="mt-2" />
    </div>

    <div class="mt-6">
        <x-primary-button>
            {{ __('Chirp') }}
        </x-primary-button>
    </div>
</form>
```

</x-fenced-code>

This partial is making use a Blade component that doesn't exist yet called `x-textarea-input`, let's create it:

<x-fenced-code file="resources/views/components/textarea-input.blade.php" copy>

```blade 
@props(['disabled' => false, 'value' => ''])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) !!}>{{ $value }}</textarea>
```

</x-fenced-code>

That's it! Refresh the page in your browser to see your new form rendered in the default layout provided by Breeze!

![Creating Chirps Link](/assets/images/bootcamp/creating-chirps-link.png?v=4)

If you click on that link, you will see the form to create Chirps and the breadcrumbs should also have been updated:

![Creating Chirps Form](/assets/images/bootcamp/creating-chirps-form.png?v=4)

### Navigation menu

Let's take a moment to add a link to the navigation menu provided by Turbo Breeze.

Update the `navigation` partial provided by Turbo Breeze to add a menu item for desktop screens:

<x-fenced-code file="resources/views/layouts/partials/navigation.blade.php">

```blade
<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

{+    <x-nav-link :href="route('chirps.index')" :active="request()->routeIs('chirps.*')">
        {{ __('Chirps') }}
    </x-nav-link>+}
</div>
```

</x-fenced-code>

Don't forget the responsive menu:

<x-fenced-code file="resources/views/layouts/partials/navigation.blade.php">

```blade 
<!-- Responsive Navigation Menu -->
<div class="hidden group-data-[responsive-nav-open-value=true]:block sm:group-data-[responsive-nav-open-value=true]:hidden">
    <div class="pt-2 pb-3 space-y-1">
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            {{ __('Dashboard') }}
        </x-responsive-nav-link>

{+        <x-responsive-nav-link :href="route('chirps.index')" :active="request()->routeIs('chirps.*')">
            {{ __('Chirps') }}
        </x-responsive-nav-link>+}
    </div>
</div>
```

</x-fenced-code>

We should see the Chirps link on the page nav now:

![Chirps Nav Link](/assets/images/bootcamp/creating-chirps-nav-link.png?v=4)

## Saving the Chirp

Our form has been configured to post messages to the `chirps.store` route that we created earlier. Let's update the `store` action on our `ChirpController` class to validate the data and create a new Chirp:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php 
<?php

// ...

class ChirpController extends Controller
{
    // ...

    public function store(Request $request)
    {
{-        //-}
{+        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->chirps()->create($validated);

        return redirect()->route('chirps.index');+}
    }

    // ...
}
```

</x-fenced-code>

We're using Laravel's powerful validation feature to ensure that the user provides a message and that it won't exceed the 255 character limit of the database column we'll be creating.

We're then creating a record that will belong to the logged in user by leveraging a `chirps` relationship. We will define that relationship soon.

Finally, we can return a redirect response to our `chirps.index` route.

### Creating a relationship

You may have noticed in the previous step that we called a `chirps` method on the `$request->user()` object. We need to create this method on our `User` model to define a ["has many"](https://laravel.com/docs/eloquent-relationships#one-to-many) relationship:

<x-fenced-code file="app/Models/User.php">

```php 
<?php

namespace App\Models;

// ...

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ...

{+    public function chirps()
    {
        return $this->hasMany(Chirp::class);
    }+}
}
```

</x-fenced-code>

Laravel offers many different types of model relationships that you can read more about in the [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships) documentation.

### Mass assignment protection

Passing all of the data from a request to your model can be risky. Imagine you have a page where users can edit their profiles. If you were to pass the entire request to the model, then a user could edit any column they like, such as an `is_admin` column. This is called a [mass assignment vulnerability](https://en.wikipedia.org/wiki/Mass_assignment_vulnerability).

Laravel protects you from accidentally doing this by blocking mass assignment by default. Mass assignment is very convenient though, as it prevents you from having to assign each attribute one-by-one. We can enable mass assignment for safe attributes by marking them as "fillable".

Let's add the `$fillable` property to our `Chirp` model to enable mass-assignment for the `message` attribute:

<x-fenced-code file="app/Models/Chirp.php">

```php 
<?php

// ...

class Chirp extends Model
{
    use HasFactory;

{+    protected $fillable = [
        'message',
    ];+}
}
```

</x-fenced-code>

You can learn more about Laravel's mass assignment protection in the [documentation](https://laravel.com/docs/eloquent#mass-assignment).

### Updating the migration

The only thing missing is extra columns in our database to store the relationship between a `Chirp` and its `User` and the message itself. Remember the database migration we created earlier? It's time to open that file to add some extra columns:

<x-fenced-code file="database/migrations/{timestamp}_create_chirps_table.php">

```php 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chirps', function (Blueprint $table) {
            $table->id();
{+            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message');+}
            $table->timestamps();
        });
    }

    // ...
};
```

</x-fenced-code>

We haven't migrated the database since we added this migration, so let's do it now:

```bash
php artisan migrate
```

Each database migration will only be run once. To make additional changes to a table, you will need to create another migration. During development, you may wish to update an undeployed migration and rebuild your database from scratch using the `php artisan migrate:fresh` command.

### Testing it out

We're now ready to send a Chirp using the form we just created! We won't be able to see the result yet because we haven't displayed existing Chirps on the page.

![Saving Chirps](/assets/images/bootcamp/creating-chirps-saving.png?v=4)

If you leave the message field empty, or enter more than 255 characters, then you'll see the validation in action.

### Artisan Tinker

This is great time to learn about [Artisan Tinker](https://laravel.com/docs/artisan#tinker), a _REPL_ ([Read-eval-print loop](https://en.wikipedia.org/wiki/Read%E2%80%93eval%E2%80%93print_loop)) where you can execute arbitrary PHP code in your Laravel application.

In your console, start a new tinker session:

```bash
php artisan tinker
```

Next, execute the following code to display the Chirps in your database:

```php
App\Models\Chirp::all();
```

```bash
=> Illuminate\Database\Eloquent\Collection {#4634
     all: [
       App\Models\Chirp {#4636
         id: 1,
         user_id: 1,
         message: "Hello World!",
         created_at: "2023-11-26 19:53:33",
         updated_at: "2023-11-26 19:53:33",
       },
     ],
   }
```

You may exit Tinker by using the `exit` command, or by pressing `Ctrl` + `c`.

## Flash Messages

Before we move on from creating Chirps, let's add the ability to show flash messages to the users. This may be useful to tell them that something happened in our app.

Since we're redirecting the user to another page and redirects happens in the browser (client side), we'd need a way to store messages across requests. Laravel has a feature called [Flash Data](https://laravel.com/docs/session#flash-data) which does exactly that! With that, we can safely store a flash message in the user's session, just so we can retrive it from there after the redirect happens in the user's browser.

Let's update our `store` action in the `ChirpController` to also return a flash message named `notice` in the redirect:

<x-fenced-code file="app/Http/Controllers/ChirpController.php">

```php 
<?php

// ...

class ChirpController extends Controller
{
    // ...

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->chirps()->create($validated);

{-        return redirect(route('chirps.index'));-}
{+        return redirect(route('chirps.index'))->with('notice', __('Chirp created.'));+}
    }

    // ...
}
```

</x-fenced-code>

Then, let's change our `layouts.app` file to include a `layouts.partials.notifications` partial:

<x-fenced-code file="resources/views/layouts/app.blade.php">

```blade 
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.partials.navigation')
{+        @include('layouts.partials.notifications')+}

        <!-- ... -->
    </div>
</body>
```

</x-fenced-code>

Next, let's create the `layouts.partials.notifications` wrapper partial:

<x-fenced-code file="resources/views/layouts/partials/notifications.blade.php" copy>

```blade 
<div id="notifications" class="fixed top-10 left-0 right-0 flex flex-col items-center justify-center space-y-2 z-10 opacity-80">
    @if (session()->has('notice'))
        @include('layouts.partials.notice', ['message' => session('notice')])
    @endif
</div>
```

</x-fenced-code>

So, each notification will render with the `layouts.partials.notice` (singular) partial and will be added to the wrapper partial. Let's add the individual notification partial:

<x-fenced-code file="resources/views/layouts/partials/notice.blade.php" copy>

```blade
<div data-turbo-temporary data-controller="flash" data-action="animationend->flash#remove" class="py-1 px-4 leading-7 text-center text-white rounded-full bg-gray-900 transition-all animate-appear-then-fade-out">
    {{ $message }}
</div>
```

</x-fenced-code>

There are a few attributes I'd like to briefly discuss here:

- The `data-turbo-temporary` tells Turbo to remove this element from the [Page Cache](https://turbo.hotwired.dev/handbook/building#preparing-the-page-to-be-cached)
- The `data-controller="flash"` is how we bind Stimulus controllers to an element. In this case, we're binding the `flash` controller, which is a controller that ships with Turbo Breeze and may be found at `resources/js/controllers/flash_controller.js`
- The `data-action="animationend->flash#remove"` is how we add event listeners and invoke Stimulus controller actions when those events happens. In this case, we're listening to a CSS3 event called `animationend` which is fired whenever a CSS animation ends. The animation is the one provided by the `animation-appear-then-fade-out` CSS class, it also comes from Turbo Breeze.

Now, build our Tailwind CSS styles:

```bash
php artisan tailwindcss:build
```

If you create another Chirp now, you should see a nice notification message at the top:

![Flash Messages](/assets/images/bootcamp/creating-chirps-flash-messages.png?v=4)
