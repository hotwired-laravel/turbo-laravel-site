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
<x-layouts.app :title="__('Chirps')">
    <section class="w-full lg:max-w-lg mx-auto">
        <div class="flex items-center space-x-2 justify-between">
            <x-text.heading size="xl">{{ __('Chirps') }}</x-text.heading>

            <a href="{{ route('chirps.create') }}" class="btn btn-primary btn-sm">{{ __('Write') }}</a>
        </div>
    </section>
</x-layouts.app>
```

</x-fenced-code>

Then, let's create our `chirps.create` page view with the Chirps form:

<x-fenced-code file="resources/views/chirps/create.blade.php" copy>

```blade 
<x-layouts.app :title="__('New Chirp')">
    <section class="w-full lg:max-w-lg mx-auto">
        <x-back-link :href="route('chirps.index')">{{ __('Chirps') }}</x-back-link>
        <x-text.heading size="xl">{{ __('New Chirp') }}</x-text.heading>
        <x-text.subheading>{{ __('Write a message to the World.') }}</x-text.subheading>

        <x-page-card class="my-6">
            <x-turbo::frame id="create_chirp" target="_top">
                @include('chirps.partials.form')
            </x-turbo::frame>
        </x-page-card>
    </section>
</x-layouts.app>
```

</x-fenced-code>

Again, this view is including a `form` partial. Create that file with the following content:

<x-fenced-code file="resources/views/chirps/partials/form.blade.php" copy>

```blade 
<form action="{{ route('chirps.store') }}" method="post" class="w-full space-y-6">
    @csrf

    <!-- Content -->
    <div>
        <x-form.label for="message">{{ __('What\'s on your mind?') }}</x-form.label>

        <x-form.textarea-input
            id="message"
            name="message"
            :value="old('message')"
            :data-error="$errors->has('message')"
            required
            autofocus
            autocomplete="off"
            :placeholder="strip_tags(Illuminate\Foundation\Inspiring::quote())"
            class="mt-2"
        />

        <x-form.error :message="$errors->first('content')" />
    </div>

    <div class="flex items-center justify-start gap-4">
        <x-form.button.primary type="submit">{{ __('Post') }}</x-form.button.primary>
    </div>
</form>
```

</x-fenced-code>

This partial is making use a Blade component that doesn't exist yet called `x-textarea-input`, let's create it:

<x-fenced-code file="resources/views/components/form/textarea-input.blade.php" copy>

```blade 
@props(['value'])

<textarea {{ $attributes->merge([
    'class' => 'w-full textarea data-error:textarea-error',
]) }}>{{ $value }}</textarea>
```

</x-fenced-code>

That's it! Refresh the page in your browser to see your new form rendered in the default layout provided by Breeze!

![Creating Chirps Link](/assets/images/bootcamp/creating-chirps-link.png?v=1)

If you click on that link, you will see the form to create Chirps and the breadcrumbs should also have been updated:

![Creating Chirps Form](/assets/images/bootcamp/creating-chirps-form.png?v=1)

### Navigation menu

Update the nav links in the header partial to add a menu item for desktop screens:

<x-fenced-code file="resources/views/components/layouts/app/header.blade.php">

```blade
<x-navbar class="-mb-px max-lg:hidden">
    <x-navbar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')">
        <span>{{ __('Dashboard') }}</span>
    </x-navbar.item>

{+    <x-navbar.item icon="inbox" :href="route('chirps.index')" :current="request()->routeIs('chirps.*')">
        <span>{{ __('Chirps') }}</span>
    </x-navbar.item>+}
</x-navbar>
```

</x-fenced-code>

Don't forget the responsive menu:

<x-fenced-code file="resources/views/components/layouts/app/header.blade.php">

```blade 
<x-sidebar.navlist class="px-0">
    <x-sidebar.navlist-item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-sidebar>
{+    <x-sidebar.navlist-item icon="inbox" :href="route('chirps.index')" :current="request()->routeIs('chirps.*')">{{ __('Chirps') }}</x-sidebar>+}
</x-sidebar.navlist>
```

</x-fenced-code>

Next, we need to add

We should see the Chirps link on the page nav now:

![Chirps Nav Link](/assets/images/bootcamp/creating-chirps-nav-link.png?v=1)

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

        return to_route('chirps.index')->with('notice', __('Chirp posted.'));+}
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

Each database migration will only run once. To make additional changes to a table, you will need to create another migration. During development, you may wish to update an undeployed migration and rebuild your database from scratch using the `php artisan migrate:fresh` command.

### Testing it out

We're now ready to send a Chirp using the form we just created! We won't be able to see the result yet because we haven't displayed existing Chirps on the page.

![Saving Chirps](/assets/images/bootcamp/creating-chirps-saving.png?v=1)

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
