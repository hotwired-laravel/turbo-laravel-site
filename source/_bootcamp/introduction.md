---
extends: _layouts.bootcamp
title: Introduction
description: Learn how to make Hotwired apps using Laravel.
order: 1
---

# Introduction

Lean how to make [Hotwired](https://hotwired.dev/) web apps using Laravel. And when we're finished with the web app, we'll dive into the Hotwire Native side of Hotwire so we can see how it bridges the web and native worlds!

To explore the many sides of Hotwire, we'll build a micro-blogging platform called Turbo Chirper. Many parts of this tutorial were inspired by the [official Laravel Bootcamp](https://bootcamp.laravel.com/) adapted to work better in a Hotwired app.

We'll use [Importmap Laravel](https://github.com/tonysm/importmap-laravel) and [TailwindCSS Laravel](https://github.com/tonysm/tailwindcss-laravel) instead of Laravel's default Vite setup. Vite would work, but I'm taking this opportunity to demonstrate an alternative front-end setup. If you're already familiar with Vite, feel free to choose the `turbo-vite` stack when setting up the application in the [installation guide](/bootcamp/installation).

On the JavaScript side, we'll use [Stimulus.js](https://stimulus.hotwired.dev/). [Turbo Breeze](https://github.com/hotwired-laravel/turbo-breeze) - the starter kit we'll use - comes with all the same components in Laravel Breeze, reimplemented in Stimulus, so you won't miss out on anything. Also, most of the time, we're able to quickly convert Alpine components into Stimulus controllers.

Let's get started!

## Web

In the Web Tutorial, we're gonna build our [majestic web app](https://m.signalvnoise.com/the-majestic-monolith/) using [Laravel](https://laravel.com/) and [Turbo Laravel](https://github.com/hotwired-laravel/turbo-laravel) that will serve as basis for the second part of the tutorial which focuses on Hotwire Native and Android.

[Start the Web Tutorial...](/guides/installation)

## Native

The second part of this Bootcamp will focus on Hotwire Native. The goal is to showcase the Native side of Hotwire. We're going to use Android and Kotlin to build a fully native wrapper around our web app and [progressively enhance the UX for mobile users](https://m.signalvnoise.com/basecamp-3-for-ios-hybrid-architecture/).
