---
extends: _layouts.bootcamp
title: Creating Chirps
description: Creating Chirps
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

```php
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePasswordController;
use Illuminate\Support\Facades\Route;

// ...

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('chirps', ChirpController::class) 
    ->only(['index', 'create', 'store'])
    ->middleware(['auth', 'verified']); 

Route::middleware('auth')->group(function () {
    // ...
});

require __DIR__.'/auth.php';
```

This will create the following routes:

| Verb | URI | Action | Route Name |
|---|---|---|---|
| GET | `/chirps` | index | `chirps.index` |
| GET | `/chirps/create` | create | `chirps.create` |
| POST | `/chirps` | store | `chirps.store` |

You may view all of the routes for your application by running the `php artisan route:list` command.

Let's test our route and controller by returning a test message from the `index` method of our new `ChirpController` class:

```php 
<?php
// [tl! collapse:start]
namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
// [tl! collapse:end]
class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return 'Hello, World!'; // [tl! remove:-1,1 add]
    }
    // [tl! collapse:start]
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chirp $chirp)
    {
        //
    }
    // [tl! collapse:end]
}
```

If you are still logged in from earlier, you should see your message when navigating to [http://localhost:8000/chirps](http://localhost:8000/chirps), or [http://localhost/chirps](http://localhost/chirps) if you're using Sail!

### Adding The Form

Let's update our `index` action in the `ChirpController` to render the view that will display the listing of Chirps and a link to create a Chirp. We'll also update the `create` action to render the view that will display the form to create Chirps:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 'Hello, World!';
        return view('chirps.index', [ // [tl! remove:-1,1 add:start]
            //
        ]);// [tl! add:end]
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('chirps.create', [ // [tl! remove:-1,1 add:start]
            //
        ]); // [tl! add:end]
    }
    // [tl! collapse:start]
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chirp $chirp)
    {
        //
    }
    // [tl! collapse:end]
}
```

We can then create our `chirps.index` view with a link to our form for creating new Chirps:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[__('Chirps')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    @include('chirps.partials.new-chirp-trigger')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

This view is including a partial called `new-chirp-trigger`, so create the partial with the following content:

```blade 
<div class="relative flex items-center justify-center py-10 px-4 rounded-lg border border-dotted border-gray-300 dark:border-gray-600">
    <a class="text-gray-700 dark:text-gray-500" href="{{ route('chirps.create') }}">
        {{ __('New Chirp') }}
        <span class="absolute inset-0"></span>
    </a>
</div>
```

Then, let's create our `chirps.create` page view with the Chirps form:

```blade 
<x-app-layout :title="__('Create Chirp')">
    <x-slot name="header">
        <h2 class="flex items-center space-x-1 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-breadcrumbs :links="[route('chirps.index') => __('Chirps'), __('New Chirp')]" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    @include('chirps.partials.form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

Again, this view is including a `form` partial. Create that file with the following content:

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

This partial is making use a Blade component that doesn't exist yet called `x-textarea-input`, let's create it:

```blade 
@props(['disabled' => false, 'value' => ''])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) !!}>{{ $value }}</textarea>
```

That's it! Refresh the page in your browser to see your new form rendered in the default layout provided by Breeze!

![Creating Chirps Link](/images/bootcamp/creating-chirps-link.png)

If you click on that link, you will see the form to create Chirps and the breadcrumbs should also have been updated:

![Creating Chirps Form](/images/bootcamp/creating-chirps-form.png)

### Navigation menu

Let's take a moment to add a link to the navigation menu provided by Turbo Breeze.

Update the `navigation` partial provided by Turbo Breeze to add a menu item for desktop screens:

```blade 
<nav
    data-controller="responsive-nav"
    data-action="
        turbo:before-cache@window->responsive-nav#close
        click@window->responsive-nav#closeWhenClickedOutside
    "
    class="group bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700"
>
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('chirps.index')" :active="request()->routeIs('chirps.*')">
                        {{ __('Chirps') }}
                    </x-nav-link> <!-- [tl! add:-2,3] -->
                </div>
            </div>
            <!-- [tl! collapse:start] -->
            <div class="flex items-center space-x-2">
                <!-- Settings Dropdown -->
                <a href="{{ route('profile.index') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>{{ Auth::user()->name }}</div>
                </a>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button data-action="responsive-nav#toggle" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path class="inline-flex group-data-[responsive-nav-open-value=true]:hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path class="hidden group-data-[responsive-nav-open-value=true]:inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- [tl! collapse:end] -->
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div class="hidden group-data-[responsive-nav-open-value=true]:block sm:group-data-[responsive-nav-open-value=true]:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('chirps.index')" :active="request()->routeIs('chirps.*')">
                {{ __('Chirps') }}
            </x-responsive-nav-link> <!-- [tl! add:-2,3] -->
        </div>
    </div>
</nav>
```

We should see the Chirps link on the page nav now:

![Chirps Nav Link](/images/creating-chirps-nav-link.png)

## Saving the Chirp

Our form has been configured to post messages to the `chirps.store` route that we created earlier. Let's update the `store` action on our `ChirpController` class to validate the data and create a new Chirp:

```php 
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
        return view('chirps.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('chirps.create');
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
        //
        $validated = $request->validate([ // [tl! remove:-1,1 add:start]
            'message' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->chirps()->create($validated);

        return redirect()->route('chirps.index'); // [tl! add:end]
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chirp $chirp)
    {
        //
    }
    // [tl! collapse:end]
}
```

We're using Laravel's powerful validation feature to ensure that the user provides a message and that it won't exceed the 255 character limit of the database column we'll be creating.

We're then creating a record that will belong to the logged in user by leveraging a `chirps` relationship. We will define that relationship soon.

Finally, we can return a redirect response to our `chirps.index` route.

### Creating a relationship

You may have noticed in the previous step that we called a `chirps` method on the `$request->user()` object. We need to create this method on our `User` model to define a ["has many"](https://laravel.com/docs/eloquent-relationships#one-to-many) relationship:

```php 
<?php
// [tl! collapse:start]
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// [tl! collapse:end]
class User extends Authenticatable
{
    // [tl! collapse:start]
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function chirps()
    {
        return $this->hasMany(Chirp::class);
    } // [tl! add:-3,4]
}
```

Laravel offers many different types of model relationships that you can read more about in the [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships) documentation.

### Mass assignment protection

Passing all of the data from a request to your model can be risky. Imagine you have a page where users can edit their profiles. If you were to pass the entire request to the model, then a user could edit any column they like, such as an `is_admin` column. This is called a [mass assignment vulnerability](https://en.wikipedia.org/wiki/Mass_assignment_vulnerability).

Laravel protects you from accidentally doing this by blocking mass assignment by default. Mass assignment is very convenient though, as it prevents you from having to assign each attribute one-by-one. We can enable mass assignment for safe attributes by marking them as "fillable".

Let's add the `$fillable` property to our `Chirp` model to enable mass-assignment for the `message` attribute:

```php 
<?php
// [tl! collapse:start]
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// [tl! collapse:end]
class Chirp extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
    ]; // [tl! add:-2,3]
}
```

You can learn more about Laravel's mass assignment protection in the [documentation](https://laravel.com/docs/eloquent#mass-assignment).

### Updating the migration

The only thing missing is extra columns in our database to store the relationship between a `Chirp` and its `User` and the message itself. Remember the database migration we created earlier? It's time to open that file to add some extra columns:

```php 
<?php
// [tl! collapse:start]
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// [tl! collapse:end]
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chirps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message'); // [tl! add:-1,2]
            $table->timestamps();
        });
    }
    // [tl! collapse:start]
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chirps');
    }
    // [tl! collapse:end]
};
```

We haven't migrated the database since we added this migration, so let do it now:

```bash
php artisan migrate
```

Each database migration will only be run once. To make additional changes to a table, you will need to create another migration. During development, you may wish to update an undeployed migration and rebuild your database from scratch using the `php artisan migrate:fresh` command.

### Testing it out

We're now ready to send a Chirp using the form we just created! We won't be able to see the result yet because we haven't displayed existing Chirps on the page.

![Saving Chirps](/images/bootcamp/creating-chirps-saving.png)

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

```php 
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
        return view('chirps.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('chirps.create');
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

        $request->user()->chirps()->create($validated);

        return redirect()->route('chirps.index');
        return redirect()->route('chirps.index')->with('notice', __('Chirp created.'));  // [tl! remove:-1,1 add]
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chirp  $chirp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chirp $chirp)
    {
        //
    }
    // [tl! collapse:end]
}
```

Then, let's change our `layouts.app` file to include a `layouts.partials.notifications` partial:

```blade 
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- [tl! collapse:start] -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- [tl! collapse:end] -->
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.partials.navigation')
            @include('layouts.partials.notifications') <!-- [tl! add]-->
            <!-- [tl! collapse:start] -->
            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            <!-- [tl! collapse:end] -->
        </div>
    </body>
</html>
```

Next, let's create the `layouts.partials.notifications` wrapper partial:

```blade 
<div id="notifications" class="fixed top-10 left-0 right-0 flex flex-col items-center justify-center space-y-2 z-10 opacity-80">
    @if (session()->has('notice'))
        @include('layouts.partials.notice', ['message' => session('notice')])
    @endif
</div>
```

So, each notification will render with the `layouts.partials.notice` (singular) partial and will be added to the wrapper partial. Let's add the indivitual notification partial:

```blade 
<div data-turbo-temporary data-controller="flash" data-action="animationend->flash#remove" class="py-1 px-4 leading-7 text-center text-white rounded-full bg-gray-900 transition-all animate-appear-then-fade-out">
    {{ $message }}
</div>
```

There are a few attributes I'd like to briefly discuss here:

- The `data-turbo-temporary` tells Turbo to remove this element from the [Page Cache](https://turbo.hotwired.dev/handbook/building#preparing-the-page-to-be-cached)
- The `data-controller="flash"` is how we bind Stimulus controllers to an element. In this case, we're binding the `flash` controller, which is a controller that ships with Turbo Breeze and may be found at `resources/js/controllers/flash_controller.js`
- The `data-action="animationend->flash#remove"` is how we add event listeners and invoke Stimulus controller actions when those events happens. In this case, we're listening to a CSS3 event called `animationend` which is fired whenever a CSS animation ends. The animation is the one provided by the `animation-appear-then-fade-out` CSS class, it also comes from Turbo Breeze.

Now, build our TailwindCSS styles:

```bash
php artisan tailwindcss:build
```

If you create another Chirp now, you should see a nice notification message at the top:

![Flash Messages](/images/bootcamp/creating-chirps-flash-messages.png)
