---
extends: _layouts.bootcamp
title: Installation
description: Installation
order: 2
---

# *01.* Installation

Our first step is to create the web app and setup our local environment.

## Installing Laravel

There are two paths in here: one uses a local installation setup, and another one that uses [Laravel Sail](https://laravel.com/docs/sail). Choose how you're going to run the app locally as you feel more comfortable.

### Quick Installation

If you have already installed PHP and Composer on your local machine, you may create a new Laravel project via [Composer](https://getcomposer.org/):

```bash
composer create-project laravel/laravel turbo-chirper
```

After the project has been created, start Laravel's local development server using the Laravel's Artisan CLI serve command:

```bash
cd turbo-chirper/

php artisan serve
```

Once you have started the Artisan development server, your application will be accessible in your web browser at [http://localhost:8000](http://localhost:8000).

![Laravel Welcome page](/images/bootcamp/laravel-welcome.png)

For simplicity, you may use SQLite to store your application's data.

### Installing via Docker

If you do not have PHP installed locally, you may develop your application using [Laravel Sail](https://laravel.com/docs/sail), a light-weight command-line interface for interacting with Laravel's default Docker development environment, which is compatible with all operating systems. Before we get started, make sure to install [Docker](https://docs.docker.com/get-docker/) for your operating system. For alternative installation methods, check out Laravel's full [installation guide](https://laravel.com/docs/installation).

The easiest way to install Laravel is using Laravel's `laravel.build` service, which will download and create a fresh Laravel application for you. Launch a terminal and run the following command:

```bash
curl -s "https://laravel.build/turbo-chirper?with=none" | bash
```

Sail installation may take several minutes while Sail's application containers are built on your local machine.

Notice that we're passing the `?with=none` flag to the command. By default, the installer will pre-configure Laravel Sail with a number of useful services for your application. We don't want that. We only need the app container and we'll use SQLite, which should be the default.

After the project has been created, you can navigate to the application directory and start Laravel Sail:

```bash
cd turbo-chirper

./vendor/bin/sail up -d
```

> **note**
> You may [create a shell alias](https://laravel.com/docs/sail#configuring-a-shell-alias) that allows you execute Sail's commands more easily.

When developing applications using Sail, you may execute Artisan, NPM, and Composer commands via the Sail CLI instead of invoking them directly:

```bash
./vendor/bin/sail php --version
./vendor/bin/sail artisan --version
./vendor/bin/sail composer --version
./vendor/bin/sail npm --version
```

Once the application's Docker containers have been started, you can access the application in your web browser at: [http://localhost](http://localhost).

![Welcome Page over Sail](/images/bootcamp/laravel-welcome-sail.png)

## Installing Turbo Breeze

Next, we'll give our application a head-start by installing [Turbo Breeze](https://github.com/hotwired-laravel/turbo-breeze), a minimal, simple imeplementation of all of Laravel's authentication features, including login, registration, password reset, email verification, and password confirmation. Once installed, you are welcome to customize the components to suit your needs.

Turbo Breeze offers two stack options: `turbo`, which comes with [Importmap Laravel](https://github.com/tonysm/importmap-laravel) and [TailwindCSS Laravel](https://github.com/tonysm/tailwindcss-laravel) installed for a Node-less setup, and a `turbo-vite` option, which relies on having Node and NPM. For this tutorial, we'll be using `turbo`.

Open a new terminal in your `turbo-chirper` project directory and install your chosen stack with the given commands:

```bash
composer require hotwired-laravel/turbo-breeze:1.x-dev --dev

php artisan turbo-breeze:install turbo --dark
```

> **note**
> If you're using Sail, remember to prefix this command with `./vendor/bin/sail`, since the symlink needs to be created inside the container.

Turbo Breeze will install and configure your front-end dependencies for you. It should have built the initial version of our assets for us.

The welcome page should now have the Login and Register links at the top:

![Welcome with Auth](/images/bootcamp/install-welcome-auth.png)

And you should be able to head to the `/register` route and create your own account:

![Register Page](/images/bootcamp/install-register.png)

Then, you should be redirected to the Dashboard page:

![Dashboard Page](/images/bootcamp/install-dashboard.png)

This Dashboard page is protected by Laravel's auth middleware, so only authenticated users can access it. The registration process automatically authenticates us.

Turbo Breeze is a fork of Laravel Breeze, but customized to work better in a Hotwired context. It comes with all the same components as Laravel Breeze does, except they were rewritten in Stimulus. For an introduction to Stimulus, head out to the [Stimulus Handbook](https://stimulus.hotwired.dev/handbook/introduction).

There are a couple differences between Turbo Breeze and Larave Breeze. In Laravel Breeze, your name at the top of the navigation bar is a dropdown. In Turbo Breeze, it's a link to a page with the menu:

![Profile Menu](/images/bootcamp/profile-menu.png)

In Laravel Breeze, all the profile forms are rendered in the same page. In Turbo Breeze, each one has its own dedicated page. That's not a requirement for Hotwired apps, but it works best in a mobile context. We'll see more about that later in this bootcamp.

Now we're ready for our first feature!
