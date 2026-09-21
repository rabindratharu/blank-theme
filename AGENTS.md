# AGENTS.md

WordPress **child-theme starter scaffold** ("blank-theme", GPL-2.0). Intended to be copied and renamed into a real child theme; this repo is that scaffold checked out as its own git repo (`child-theme-update` branch).

## Naming / scaffolding — never rename tokens by hand

- The theme identity appears in every case form everywhere: `blank theme child`, `Blank Theme Child`, `BlankThemeChild`, `blank-theme-child`, `Blank-Theme-Child`, `BLANK-THEME-CHILD`, `blank_theme_child`, `Blank_Theme_Child`, `BLANK_THEME_CHILD` — plus the literal placeholder `parent-template` (the parent-theme folder, used to build the CSS dependency handle `{template}-theme-css`).
- `bin/init.js` rewrites ALL 17 forms across all files (plus filenames). Run `npm run init` (or `node bin/init.js`); it prompts interactively for the new theme name and the parent **template** folder name.
- Gotcha: `npm run prepare` → `npm run init`, so `npm install` triggers the interactive scaffolder.
- Do not partially rename tokens; a missed form breaks autoloading, textdomain, or prefixes.

## Boot & PHP layout

- `functions.php` (theme root) defines the `BLANK_THEME_CHILD_{VERSION,TEMP_DIR,BUILD_DIR,BUILD_URI}` constants, requires `inc/helpers/autoloader.php` + `inc/helpers/custom-functions.php`, then boots `Blank_Theme_Child\Inc\Blank_Theme_Child` (Singleton trait, `inc/classes/class-blank-theme-child.php`).
- Map a namespace to a file: `Blank_Theme_Child\Inc\{classes,helpers,traits}` → matching `class-*`/`trait-*` names under `inc/` (see `autoloader.php`).
- New classes go in `inc/classes/class-{name}.php` and are wired via the Singleton trait + hooks; `inc/helpers/custom-functions.php` holds small helpers.
- Template parts/templates live at theme root (child-theme override style); no block `template-parts/` structure in the scaffold.

## Assets & build (webpack via `@wordpress/scripts`)

- Webpack multi-config (`webpack.config.js`): `assets/src/js/main.js` → `assets/build/js/main.js`. **Every** `.css`/`.scss` file in `assets/src/css/` becomes its own bundle under `assets/build/css/` — add a CSS source file only if you want a new stylesheet `{handle}`.
- CSS `url()` rewriting is disabled (`url: false`); reference fonts/images with relative paths (`../images/`, `../fonts/`) — those dirs are copied verbatim by `CopyPlugin`.
- Tailwind v4 runs as a PostCSS plugin (`@tailwindcss/postcss`); content scan covers `assets/src`, `inc`, templates, root PHP (`tailwind.config.js`).
- PHP enqueues via `inc/classes/class-assets.php`, which reads generated `{name}.asset.php` for deps/version and falls back to `filemtime`/theme version.

## Commands

- Setup: `composer install` (PHP ^8.0, platform pinned 8.2) — required by `start`, `build:prod`, and lint.
- Dev watcher: `npm run start` (clean + composer install + `wp-scripts start`).
- Build: `npm run build` (dev) / `npm run build:prod` (production + `composer install --no-dev`).
- Release zip: `npm run release` → prod build → `languages/*.pot` → `grunt build` → `build/blank-theme-<version>.zip`.
- Lint (check-only, no mutations): `npm run lint:js`, `npm run lint:css`, `npm run lint:php` (parallel-lint), `composer run-script lint:phpcs` (phpcs, `phpcs.xml.dist`, WordPress-VIP-Go), `npm run lint:js:types` (tsc `--noEmit`).
- Auto-fix: `npm run lint:js:fix`, `lint:css:fix`, `lint:php:fix` (phpcbf).
- `npm run lint` runs ALL `lint:*` globs **including `lint:php:fix` (phpcbf)** — it mutates files; prefer the individual check commands.
- PHP tests: `composer run-script test` (phpunit, `phpunit.xml.dist`, coverage targets `functions.php`).
- Cypress: `npm run cypress:open`; reads `CYPRESS_BASE_URL`/`WP_USER`/`WP_PASSWORD`, baseUrl defaults to `http://elementor.local/`.

## Current repo-state gotchas (verify before trusting a command)

- `functions.php`, `style.css`, `phpcs.xml`, `yarn.lock` are **staged-deleted** in the working tree — without `functions.php`/`style.css` the theme will not activate.
- `tsconfig.json`, `phpstan.neon.dist`, `templates/`, `tests/` do not exist, so `lint:js:types` (tsc), `lint:php:stan` (phpstan), and `wp-env`-based `test:php` fail as-is. `.wp-env.json` is missing yet `test:php` targets a `wp-content/plugins/...` path (plugin-style path, dubious for a theme).
- i18n quirks: runtime/Grunt text domain is `blank-theme-child`, but `phpcs.xml.dist` enforces `blank-theme`. `npm run i18n:make-pot` scans `assets/src/**/*.js,*.php,includes/**/*.php` — the folder is `inc`, so it misses `inc/**`; POT output is `languages/porcelain.pot` (stale name).

## Conventions

- Tabs for indentation (`/.editorconfig`); WordPress Coding Standards style, short array `[]`, PHPDoc with `@package`.
- `.opencode/` ships a large WP skill library (`wpcs`, `wp-block-development`, `wp-plugin-development`, `wp-performance`, ...) — load the matching skill for the task type.