---
extends: _layouts.bootcamp
title: Installation
description: See how to install Turbo Laravel in your Laravel app.
order: 2
---

# *01.* Installation

Our first step is to create the web app and setup our local environment. This Bootcamp [assumes you have PHP installed on your system](https://php.new/).

## Installing Laravel

You may create a new Laravel project using [Composer](https://getcomposer.org/):

```bash
composer create-project laravel/laravel turbo-chirper
```

After the project has been created, start Laravel's local development server using the Laravel's Artisan CLI serve command:

```bash
cd turbo-chirper/

php artisan serve
```

Once you have started the Artisan development server, your application will be accessible in your web browser at [http://localhost:8000](http://localhost:8000).

![Laravel Welcome page](/assets/images/bootcamp/laravel-welcome.png?v=4)

By default, the Laravel app will be created using SQLite.

## Installing Turbo Breeze

Next, we'll give our application a head-start by installing [Turbo Breeze](https://github.com/hotwired-laravel/turbo-breeze), a minimal, simple implementation of all of Laravel's authentication features, including login, registration, password reset, email verification, and password confirmation. Once installed, you are welcome to customize the components to suit your needs.

Turbo Breeze offers two stack options: `turbo`, which comes with [Importmap Laravel](https://github.com/tonysm/importmap-laravel) and [TailwindCSS Laravel](https://github.com/tonysm/tailwindcss-laravel) installed for a Node-less setup, and a `turbo-vite` option, which relies on having Node and NPM. For this tutorial, we'll be using `turbo`.

Open a new terminal in your `turbo-chirper` project directory and install your chosen stack with the given commands:

```bash
composer require hotwired-laravel/turbo-breeze:1.x-dev --dev

php artisan turbo-breeze:install turbo --dark
```

Turbo Breeze will install and configure your front-end dependencies for you. It should have built the initial version of our assets for us.

The welcome page should now have the Login and Register links at the top:

![Welcome with Auth](/assets/images/bootcamp/install-welcome-auth.png?v=4)

And you should be able to head to the `/register` route and create your own account:

![Register Page](/assets/images/bootcamp/install-register.png?v=4)

Then, you should be redirected to the Dashboard page:

![Dashboard Page](/assets/images/bootcamp/install-dashboard.png?v=4)

This Dashboard page is protected by Laravel's auth middleware, so only authenticated users can access it. The registration process automatically authenticates us.

Turbo Breeze is a fork of Laravel Breeze, but customized to work better in a Hotwired context. It comes with all the same components as Laravel Breeze does, except they were rewritten in Stimulus. For an introduction to Stimulus, head out to the [Stimulus Handbook](https://stimulus.hotwired.dev/handbook/introduction).

There are a couple differences between Turbo Breeze and Laravel Breeze. In Laravel Breeze, your name at the top of the navigation bar is a dropdown. In Turbo Breeze, it's a link to a page with the menu:

![Profile Menu](/assets/images/bootcamp/profile-menu.png?v=4)

In Laravel Breeze, all the profile forms are rendered in the same page. In Turbo Breeze, each one has its own dedicated page. That's not a requirement for Hotwired apps, but it works best in a mobile context. We'll see more about that later in this bootcamp.

Now we're ready for our first feature!
