---
extends: _layouts.bootcamp
title: Introduction
description: Learn how to make Hotwired apps using Laravel.
order: 1
---

# Introduction

Lean how to make [Hotwired](https://hotwired.dev/) web apps using Laravel. And when we're finished with the web app, we'll dive into the Hotwire Native side of Hotwire so we can see how it bridges the web and native worlds!

To explore the many sides of Hotwire, we'll build a micro-blogging platform called Turbo Chirper. Many parts of this tutorial were inspired by the [official Laravel Bootcamp](https://github.com/laravel/bootcamp.laravel.com) adapted to work better in a Hotwire context.

We'll use the [Hotwire Starter Kit](https://github.com/hotwired-laravel/hotwire-starter-kit), which sets up a fresh Laravel app with the following packages already installed and configured for us:

- [Importmap Laravel](https://github.com/tonysm/importmap-laravel) which will take care of loading our JavaScript without the need for a bundler
- [Tailwind CSS Laravel](https://github.com/tonysm/tailwindcss-laravel) which will compile our Tailwind CSS styles using the Tailwind CLI so we also don't need a bundlerr
- [Turbo Laravel](https://github.com/hotwired-laravel/turbo-laravel) which installs Turbo and provides a bunch of Hotwire helpers for us to use
- [Stimulus Laravel](https://github.com/hotwired-laravel/stimulus-laravel) which installs Stimulus and adds some convenience on our workflow, like a make command for Stimulus controllers and Hotwire Native Bridge components
- [Hotreload](https://github.com/hotwired-laravel/hotreload) so when we make changes to our Blade files, JavaScript, or CSS, the browser will automatically reload things for us

All you need is [PHP installed](https://php.new/) on the latest version and that's about it.

## Web

In the Web Tutorial, we're gonna build our [majestic web app](https://m.signalvnoise.com/the-majestic-monolith/) using [Laravel](https://laravel.com/) and [Turbo Laravel](https://github.com/hotwired-laravel/turbo-laravel) that will serve as basis for the second part of the tutorial which focuses on Hotwire Native and Android.

[Start the Web Tutorial...](/bootcamp/installation)

## Native

The second part of this Bootcamp will focus on Hotwire Native. The goal is to showcase the Native side of Hotwire. We're going to use Android and Kotlin to build a fully native wrapper around our web app and [progressively enhance the UX for mobile users](https://m.signalvnoise.com/basecamp-3-for-ios-hybrid-architecture/).

[Start the Native Tutorial...](/bootcamp/native-setup)
