---
extends: _layouts.bootcamp
title: Installation
description: See how to install Turbo Laravel in your Laravel app.
order: 2
---

# *01.* Installation

Our first step is to create the web app and setup our local environment. This Bootcamp [assumes you have PHP installed on your system](https://php.new/).

## Installing Laravel

We'll create a fresh Laravel project using Laravel's installer. If you don't yet have the installer, you may install it using [Composer](https://getcomposer.org/):

```bash
composer global require laravel/installer
```

Next, we'll create the fresh Laravel project using the [Hotwire Starter Kit](https://github.com/hotwired-laravel/hotwire-starter-kit):

```bash
laravel new turbo-chirper --using=hotwired-laravel/hotwire-starter-kit --pest
```

Answer "no" to the question about NPM. After the project has been created, start Laravel's local development server using the Composer `dev` script:

```bash
cd turbo-chirper/
composer run dev
```

Once you have started the Artisan development server, your application will be accessible in your web browser at [http://localhost:8000](http://localhost:8000).

![Laravel Welcome page](/assets/images/bootcamp/laravel-welcome.png?v=1)

By default, the Laravel app will be created using SQLite. The welcome page should have the Login and Register links at the top. You should be able to head to the `/register` route and create your own account:

![Register Page](/assets/images/bootcamp/install-register.png?v=1)

Then, you should be redirected to the Dashboard page:

![Dashboard Page](/assets/images/bootcamp/install-dashboard.png?v=1)

This Dashboard page is protected by Laravel's auth middleware, so only authenticated users can access it. The registration process automatically authenticates us.

If you click on your profile, you will see a settings menu screen with a few options of settings you may change:

![Profile Menu](/assets/images/bootcamp/profile-menu.png?v=1)

Click on the edit profile link will go to the edit profile page:

![Edit Profile](/assets/images/bootcamp/profile-edit.png?v=1)

Here's the change password page:

![Change Password](/assets/images/bootcamp/profile-change-password.png?v=1)

In the main nav there's a theme switcher which allows you to change the daisyUI theme:

![Theme Switcher](/assets/images/bootcamp/themes.png?v=1)

Let's build our first feature!
