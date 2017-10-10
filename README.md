<p align="center">
    <img src="https://user-images.githubusercontent.com/7388088/30937714-e6537c94-a3d7-11e7-83e8-3052addc7ba0.png">
</p>

# Media Manager

- Inspired by [Voyager](https://github.com/the-control-group/voyager), [October](https://github.com/octobercms/october), [WordPress](https://codex.wordpress.org/Media_Library_Screen)

- to optimize uploaded files on the fly try [approached](https://github.com/approached/laravel-image-optimizer) or [spatie](https://github.com/spatie/laravel-image-optimizer)

- package requires Laravel v5.4+

## Installation

- `composer require ctf0/media-manager`

- (Laravel < 5.5) add the service provider to `config/app.php`

```php
'providers' => [
    ctf0\MediaManager\MediaManagerServiceProvider::class,
]
```

- publish the package assets with

`php artisan vendor:publish --provider="ctf0\MediaManager\MediaManagerServiceProvider"`

- after installation, package will auto-add
    + package routes to `routes/web.php`
    + package assets compiling to `webpack.mix.js`
    + `MIX_MM_FRAMEWORK=bulma` to `.env`

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
    + name "default"
    + size
    + last modified
- items count for
    + all
    + selected
    + search found
- protection against overwriting (files / folders)
- autoplay media files "if selected filter is audio/video"
- file name sanitization for
    + upload
    + rename
    + new folder
- disable/enable buttons depend on the usage to avoid noise & keep the user focused
- shortcuts

    |      navigation     |             button            |  keyboard | mouse (click) |
    |---------------------|-------------------------------|-----------|---------------|
    |                     | upload *(toolbar)*            | u         | *             |
    |                     | refresh *(toolbar)*           | r         | *             |
    |                     | move *(toolbar)*              | m         | *             |
    |                     | delete *(toolbar)*            | d/del     | *             |
    |                     | bulk select *(toolbar)*       | b         | *             |
    |                     | bulk select all *(toolbar)*   | a         | *             |
    |                     | toggle *(sidebar)*            | t         | *             |
    |                     | file rename *(modal)*         | enter     | *             |
    |                     | file delete *(modal)*         | enter     | *             |
    |                     | create new folder *(modal)*   | enter     | *             |
    | select next         |                               | right     | *             |
    | select prev         |                               | left      | *             |
    | select first        |                               | home      | *             |
    | select last         |                               | end       | *             |
    | open folder         |                               | enter     | double click  |
    | go back to prev dir | folderName *(breadcrumb)*     | backspace | *             |
    | play/pause media    | player controller *(sidebar)* | space     | *             |
    | view image          | image *(sidebar)*             | space     | *             |
    | hide image          | image *(light-box)*           | space/esc | *             |

- events

    |     event-name     |         description          |
    |--------------------|------------------------------|
    | modal-show         | when modal is showen         |
    | modal-hide         | when modal is hidden         |
    | no-files-show      | when no files msg is showen  |
    | no-files-hide      | when no files msg is hidden  |
    | loading-files-show | when loading files is hidden |
    | loading-files-hide | when loading files is hidden |
    | ajax-error-show    | when ajax call fails         |

## Config
**config/mediaManager.php**

```php
return [
    /*
     * ignore files pattern
     */
    'ignore_files' => '/^\..*/',

    /*
     * filesystem disk
     */
    'storage_disk' => 'public',

    /*
     * remove any file special chars except (. _ -)
     */
    'allowed_fileNames_chars' => '.\_\-',

    /*
     * remove any folder special chars except (_ -)
     */
    'allowed_folderNames_chars' => '\_\-',

    /*
     * disallow uploading files with the following mimetypes
     * https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    'unallowed_mimes' => ['php', 'java'],

    /*
     * when file names gets cleand up
     */
    'sanitized_text' => 'sanitized',

    /*
     * css farmework
     */
    'framework' => env('MIX_MM_FRAMEWORK'),

    /*
     * display file last modification time as
     * http://carbon.nesbot.com/docs/#api-formatting
     */
    'last_modified_format' => 'toDateString',

    /**
     * hide file extension in files list
     */
    'hide_ext' => true
];
```

## Usage

- For everyone that hate ***npm*** & possibly the whole js ecosystem **"YOU ARE NOT ALONE"** but maintaining a seperate dist copy is a tedious job for me, so if you need a help installing it, here are some steps to get you going
    + install npm https://www.npmjs.com/get-npm
    + (optional) install yarn https://yarnpkg.com/lang/en/docs/install/
    + follow the steps below :clap: :muscle: :dancers:

---

- install dependencies

```bash
yarn add vue dropzone keycode vue-tippy vue2-filters vuemit vue-notif
# or
npm install vue dropzone keycode vue-tippy vue2-filters vuemit vue-notif --save
```

- for styling we use ***bulma***

> ***Or Use another Framework***
>
> - duplicate `views/vendor/MediaManager/media-bulma` and rename it to the framework you want ex.`media-bootstrap`
> - duplicate `assets/vendor/MediaManager/js/components/media-bulma.vue` and rename it to the framework you want ex.`media-bootstrap.vue`
> - duplicate `assets/vendor/MediaManager/sass/media-bulma.scss` and rename it to the framework you want ex.`media-bootstrap.scss`
> - set `MIX_MM_FRAMEWORK` to the framework name ex.`MIX_MM_FRAMEWORK=bootstrap`
> - start editing the new files.

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    + if you are having issues with `npm run production`, [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/)

```js
// ex. "resources/assets/js/app.js"

require('./../vendor/MediaManager/js/manager')

new Vue({
    el: '#app'
})
```

- now visit `localhost:8000/media`
