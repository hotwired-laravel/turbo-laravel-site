# turbo-laravel.com

The website for [Turbo Laravel](https://github.com/hotwired-laravel/turbo-laravel): the homepage, the documentation, and the Bootcamp. It's a static site built with [Jigsaw](https://jigsaw.tighten.com), Tailwind CSS and Vite, and deployed to GitHub Pages.

## Requirements

- PHP 8.2+ and Composer
- Node.js 20.19+ (or 22.12+) and npm

## Getting started

```bash
composer install
npm install
cp .env.example .env

bin/pull      # copy the docs in from the package repository
composer dev  # Jigsaw + Vite with live reload at http://localhost:8000
```

## Where the content lives

| What | Where |
| --- | --- |
| Homepage | `source/index.blade.php` (code samples in `source/_snippets/`) |
| Bootcamp chapters | `source/_bootcamp/*.md`, sidebar in `bootcamp.php` |
| 2.x docs | `docs/` on the `2.x` branch of [hotwired-laravel/turbo-laravel](https://github.com/hotwired-laravel/turbo-laravel), sidebar in `navigation.php` |
| 1.x docs | `docs/` on the `1.x` branch of the same repository, sidebar in `v1-navigation.php` |

**The docs are not edited here.** `bin/pull` copies them into `source/_docs` and `source/_v1`, and both folders are git-ignored. Change them in the package repository, then run `bin/pull` again to see the result locally. "Edit this page on GitHub" links on the docs pages point to the package repository; on Bootcamp pages they point to this one.

### Writing Bootcamp chapters

- Number chapters in the heading: `# *04.* Editing Chirps`. The number is shown in the title and in the previous/next links.
- Use `<x-fenced-code file="path/to/file.php">` around a code block to show its filename.
- Mark changed lines inside code blocks with `{+ added +}` and `{- removed -}`.
- Use `<x-note type="info|warning|danger">` for callouts.
- Set `video: https://youtube.com/...` in the front matter to show a video card under the title.

## Project layout

- `source/_layouts/` holds the page layouts. `site` is the shared shell (header and footer), and `documentation` is shared by the docs, 1.x docs, and Bootcamp pages.
- `source/_assets/css/main.css` holds the design tokens (colors, fonts, shadows) as Tailwind theme variables. `_docs.css` styles the article content and code blocks.
- `app/` holds PHP helpers used at build time: `DocsMarkdownParser` adds the label and copy button to code blocks, and `DocContent` builds the "On this page" outline.
- `source/assets/images/turbo-laravel-meta.png` is the social card (1200×630). Its URL carries a content hash, so replacing the file is enough for social platforms to refresh it.

## Building

```bash
npm run build  # local build into build_local/
npm run prod   # production build into build_production/
```

## Deploying

Every push to `main` runs `.github/workflows/publish.yml`, which checks out the latest 1.x and 2.x docs, builds the production site, and publishes it to GitHub Pages at [turbo-laravel.com](https://turbo-laravel.com). You can also start it by hand from the Actions tab. Docs changes in the package repository only reach the site on the next run.
