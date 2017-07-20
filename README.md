# Laravel Media Manager

![Demo](./demo.jpg)

## Intro

- Inspiration by [Voyager](https://github.com/the-control-group/voyager).
- Built using
    + [Vue](https://vuejs.org/)
    + [jQuery](https://jquery.com/)
    + [dropzone](http://www.dropzonejs.com/)
    + [keycode](https://github.com/timoxley/keycode)
    + [jquery.scrollTo](https://github.com/flesler/jquery.scrollTo)
    + [vue-tippy](https://github.com/KABBOUCHI/vue-tippy)
    + [vue2-filters](https://github.com/freearhey/vue2-filters)
    + [vue-lightbox](https://github.com/phecko/vue-lightbox)
    + [bootstrap modal](http://getbootstrap.com/javascript/#modals)
    + [notification-component](https://github.com/ctf0/Notification-Component)

## Installation

- `composer require ctf0/media-manager`

- add the service provider to `config/app.php`

```php
'providers' => [
    ctf0\MediaManager\MediaManagerServiceProvider::class,
]
```

- publish the package assets with

`php artisan vendor:publish --provider="ctf0\MediaManager\MediaManagerServiceProvider"`

- install javascript dependencies

```bash
yarn add vue dropzone keycode vue-tippy vue2-filters vue-lightbox vuemit
# or
npm install vue dropzone keycode vue-tippy vue2-filters vue-lightbox vuemit
```

- for styling we use ***bulma*** so install it aswell, or [Use another Framework](#use-another-framework)

## Features

- multi
    - upload
    - move
    - delete
- bulk selection.
- auto scroll to selected item when using (left, right, home, end)
- search
- filter by type
    + folder
    + image
    + audio
    + video
    + text
- sortBy
    + name
    + size "still need some work for (kb vs mb)"
    + last modified
- items count for
    + all
    + selected
    + search found
- protection against overwriting (files / folders)
- file name sanitization for
    + upload
    + rename
    + new folder
- disable/enable buttons depend on the usage to avoid noise & keep the user focused
- shortcuts

    |      navigation     |            button           |  keyboard |    mouse     |
    |---------------------|-----------------------------|-----------|--------------|
    |                     | upload *(toolbar)*          | u         |              |
    |                     | refresh *(toolbar)*         | r         |              |
    |                     | move *(toolbar)*            | m         |              |
    |                     | delete *(toolbar)*          | d/del     |              |
    |                     | bulk select *(toolbar)*     | b         |              |
    |                     | bulk select all *(toolbar)* | a         |              |
    |                     | toggle *(sidebar)*          | t         | click        |
    |                     | file rename *(modal)*       | enter     |              |
    |                     | file delete *(modal)*       | enter     |              |
    |                     | create new folder *(modal)* | enter     |              |
    | select next         |                             | right     |              |
    | select prev         |                             | left      |              |
    | selct first         |                             | home      |              |
    | select last         |                             | end       |              |
    | open folder         |                             | enter     | double click |
    | go back to prev dir | folderName *(breadcrumb)*   | backspace | click        |
    | play/pause          | player controller           | space     |              |
    | view image          | sidebar image               | space     | click        |
    | hide image          |                             | space/esc | click        |

## Config

```php
// config/mediaManager.php

return [
    // ignore files pattern
    'ignore_files' => '/^\..*/',

    // filesystem disk
    'storage_disk'=> 'public',

    // remove any file special chars except (. _ -)
    'allowed_fileNames_chars'=> '.\_\-',

    // remove any folder special chars except (_ -)
    'allowed_folderNames_chars'=> '\_\-',

    // when file names gets cleand up
    'sanitized_text'=> 'sanitized',

    // media manager root url
    'root_url'=> '/media',

    // css farmework
    'framework'=> env('MIX_MM_FRAMEWORK'),
];
```

## Usage

- add `new MediaRoutes();` to your route file, or under any route group ex.`admin`

    \>\> **dont forget to update the `root_url`** <<

- to provide as much flexibility as possible, edit the `views/vendor/MediaManager/(framework)/media` file and let it extend from your main layout.
- add `MIX_MM_FRAMEWORK=bulma` to your `.env` file.
- run `npm run watch` to compile your `js/css` files.

#### Use another Framework

- duplicate `views/vendor/MediaManager/bulma` and rename it to the framework you want ex.`bootstrap`
- duplicate `assets/vendor/MediaManager/js/components/bulma-notif` and rename it to the framework you want ex.`bootstrap-notif`
- duplicate `assets/vendor/MediaManager/sass/bulma` and rename it to the framework you want ex.`bootstrap`
- set `MIX_MM_FRAMEWORK` to the framework name ex.`MIX_MM_FRAMEWORK=bootstrap`
- start editing the new files.

after you are done, maybe you can send me a PR so everyone else can benefit from it :trophy:

---

## ToDo "ANY HELP IS DEEPLY APPRECIATED"

* [ ] Add Support To Other Css Frameworks.
* [ ] Add Support For Editors "tinymce / Ckeditor".
