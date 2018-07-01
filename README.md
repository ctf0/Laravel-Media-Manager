# Laravel Media Manager

[![Latest Stable Version](https://img.shields.io/packagist/v/ctf0/media-manager.svg)](https://packagist.org/packages/ctf0/media-manager) [![Total Downloads](https://img.shields.io/packagist/dt/ctf0/media-manager.svg)](https://packagist.org/packages/ctf0/media-manager) [![Donate with Bitcoin](https://en.cryptobadges.io/badge/micro/16ri7Hh848bw7vxbEevKHFuHXLmsV8Vc9L)](https://en.cryptobadges.io/donate/16ri7Hh848bw7vxbEevKHFuHXLmsV8Vc9L)

<p align="center">
    <img src="https://user-images.githubusercontent.com/7388088/41806986-f9835870-76c7-11e8-95a9-534e45ccf7dd.png">
</p>

- to optimize uploaded files on the fly try [spatie](https://github.com/spatie/laravel-image-optimizer)

- package requires Laravel v5.4+

<br>

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

- [install dependencies](https://github.com/ctf0/Laravel-Media-Manager/wiki/Packages-In-Use)

  ```bash
  yarn add vue vue-ls vue-async-computed vue-tippy@v1 vue2-filters vue-notif vue-clipboard2 vue-awesome@v2 vue-touch@next idb-keyval axios dropzone cropperjs keycode date-fns lottie-web fuse.js
  # or
  npm install vue vue-ls vue-async-computed vue-tippy@v1 vue2-filters vue-notif vue-clipboard2 vue-awesome@v2 vue-touch@next idb-keyval axios dropzone cropperjs keycode date-fns lottie-web fuse.js --save
  ```

- run `npm run watch` to compile your `js/css` files.
    - if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/)
<br>

## Features

- [image editor](https://github.com/ctf0/Laravel-Media-Manager/wiki/Image-Editor)
- multi
  + upload
  + move/copy
  + delete
- toggle between `random/original` names for uploaded files
- upload an image from a url
- [load image on demand](https://github.com/ctf0/Laravel-Media-Manager/wiki/Caching-Strategies#cache-api-image-offline-caching)
- [cache requests](https://github.com/ctf0/Laravel-Media-Manager/wiki/Caching-Strategies)
- [asynchronous Updates](https://github.com/ctf0/Laravel-Media-Manager/wiki/Async-Update-The-Manager)
- bulk selection
- change item/s visibility
- update the page url while navigation
- dynamically hide [files](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Files-With-Extension) / [folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Folders)
- [restrict access to path](https://github.com/ctf0/Laravel-Media-Manager/wiki/Restrict-Access-To-Path)
- download selected ["including bulk selection"](https://github.com/ctf0/Laravel-Media-Manager/wiki/Download-Files-as-a-ZipFile)
- directly copy selected file link
- use the manager
    + [from modal](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-From-A-Modal)
    + [with any wysiwyg editor](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-With-Any-WYSIWYG-Editor)
- auto scroll to selected item when using (left, up, righ, down, home, end)
- [lock/unlock](https://github.com/ctf0/Laravel-Media-Manager/wiki/Lock-Files-&-Folder) item/s ***"sqLite must be installed"***
- search "current / global"
- filter by
  + folder
  + image
  + audio
  + video
  + text/pdf
  + locked items
- sort by
  + name "default"
  + size
  + last modified
- items count for
  + all
  + selected
  + search found
- protection against overwriting (files/folders)
- file name sanitization for
  + upload
  + rename
  + new folder
- autoplay media files ***"if selected filter is audio/video"***
- disable/enable buttons depend on the usage to avoid noise & keep the user focused
- shortcuts / gestures

  |       navigation      |                    button                   |     keyboard     |          click / tap          |              touch              |
  |-----------------------|---------------------------------------------|------------------|-------------------------------|---------------------------------|
  |                       | toggle upload panel *(toolbar)*             | u                | *                             |                                 |
  |                       | refresh *(toolbar)*                         | r                | * / hold *(clear ls & cache)* |                                 |
  |                       | move *(toolbar)*                            | m                | *                             |                                 |
  |                       | image editor *(toolbar)*                    | e                | *                             |                                 |
  |                       | delete *(toolbar)*                          | d / del          | *                             |                                 |
  |                       | lock/unlock *(toolbar)*                     | l                | *                             |                                 |
  |                       | change visibility *(toolbar)*               | v                | *                             |                                 |
  |                       | toggle bulk selection *(toolbar)*           | b                | *                             |                                 |
  |                       | (reset) bulk select all *(toolbar)*         | a                | *                             |                                 |
  |                       | toggle sidebar *(path bar)*                 | t                | *                             |                                 |
  |                       | confirm *(modal)*                           | enter            | *                             |                                 |
  |                       | toggle preview image/pdf/text *(item)*      | space            | **                            |                                 |
  |                       | play/pause media *(item)*                   | space            | **                            |                                 |
  |                       | hide (modal / upload-panel / global-search) | esc              |                               |                                 |
  |                       | reset (search / bulk selection)             | esc              |                               |                                 |
  |                       | &nbsp;                                      |                  |                               |                                 |
  |                       | move *(item)*                               |                  |                               | swipe up                        |
  |                       | delete *(item)*                             |                  |                               | swipe down                      |
  |                       | image editor *(item)*                       |                  |                               | hold                            |
  |                       | limit bulk select *(item)*                  | shift + click    |                               |                                 |
  |                       | current + next bulk select *(item)*         | alt/meta + click |                               |                                 |
  |                       | &nbsp;                                      |                  |                               |                                 |
  | select next *(item)*  |                                             | right            | *                             | swipe left  *(preview)*         |
  | select prev *(item)*  |                                             | left             | *                             | swipe right *(preview)*         |
  | select first *(item)* |                                             | home             |                               |                                 |
  | select last  *(item)* |                                             | end              |                               |                                 |
  | select next *(row)*   |                                             | down             |                               |                                 |
  | select prev *(row)*   |                                             | up               |                               |                                 |
  | open folder           |                                             | enter            | **                            |                                 |
  | go to prev dir        | folderName *(path bar)*                     | backspace        | *                             | swipe right *(items container)* |

- events

  |       type      |                 event-name                |                   description                    |
  |-----------------|-------------------------------------------|--------------------------------------------------|
  | [JS][js]        |                                           |                                                  |
  |                 | modal-show                                | when modal is showen                             |
  |                 | modal-hide                                | when modal is hidden                             |
  |                 | file_selected *(when inside modal)*       | get selected file url                            |
  |                 | multi_file_selected *(when inside modal)* | get bulk selected files urls                     |
  |                 | folder_selected *(when inside modal)*     | get selected folder path                         |
  | [Laravel][lara] |                                           |                                                  |
  |                 | MMFileUploaded($file_path)                | get uploaded file full [path][path]              |
  |                 | [MMFileSaved][event]($file_path)          | get saved(edited/link) image full [path][path]   |
  |                 | MMFileDeleted($file_path, $is_folder)     | get deleted file/folder full [path][path]        |
  |                 | MMFileRenamed($old_path, $new_path)       | get renamed file/folder old & new [path][path] |
  |                 | MMFileMoved($old_path, $new_path)         | get moved file/folder "old & new" [path][path]   |

[js]: https://github.com/gocanto/vuemit
[lara]: https://laravel.com/docs/5.5/events#manually-registering-events
[event]: https://github.com/ctf0/Laravel-Media-Manager/wiki/Image-Editor#optimize-edited-images-on-save
[path]: https://gist.github.com/ctf0/9fa6013954654384052d2e2e809b9bf6

<br>

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
     * manager controller
     */
    'controller' => '\ctf0\MediaManager\Controllers\MediaController',

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
     * other mime-types for images
     */
    'image_extended_mimes' => [
        'binary/octet-stream', // aws
    ],

    /*
     * when file names gets cleand up
     */
    'sanitized_text' => uniqid(),

    /*
     * display file last modification time as
     * http://carbon.nesbot.com/docs/#api-formatting
     */
    'last_modified_format' => 'toDateString',

    /**
     * hide file extension in files list
     */
    'hide_files_ext' => true,

    /*
     * load image preview only when item is clicked ?
     */
    'lazy_load_image_on_click' => false,

    /*
     * automatically invalidate cache after "in Minutes"
     */
    'cacheExpiresAfter' => 60,

    /*
     * in-order to get the folder items count & size
     * we need to recursively get all the files inside the folders
     * which could make the request take longer
     */
    'get_folder_info' => true,

    /**
     * do you want to enable broadcasting the changes
     * made by one user to others ?
     *
     * "laravel-echo" must be installed
     */
    'enable_broadcasting' => false
];
```

<br>

## Usage

**1-** make sure you have pre-initiated vue
```js
// app.js

window.Vue = require('vue');

new Vue({
  el: '#app'
})
```

**2-** under `resources/views/vendor/MediaManager/media.blade.php` update the path to that js file
```html
<script src="{{ asset("path/to/app.js") }}"></script>
```

**3-** visit `localhost:8000/media`

### [Check The Wiki](https://github.com/ctf0/Laravel-Media-Manager/wiki)
