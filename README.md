# Laravel Media Manager

[![Latest Stable Version](https://img.shields.io/packagist/v/ctf0/media-manager.svg)](https://packagist.org/packages/ctf0/media-manager) [![Total Downloads](https://img.shields.io/packagist/dt/ctf0/media-manager.svg)](https://packagist.org/packages/ctf0/media-manager)
[![Donate with Bitcoin](https://en.cryptobadges.io/badge/micro/16ri7Hh848bw7vxbEevKHFuHXLmsV8Vc9L)](https://en.cryptobadges.io/donate/16ri7Hh848bw7vxbEevKHFuHXLmsV8Vc9L)

<p align="center">
    <img src="https://user-images.githubusercontent.com/7388088/36298904-278ede2a-1303-11e8-8413-0bf02984019a.png">
</p>

- to optimize uploaded files on the fly try [approached](https://github.com/approached/laravel-image-optimizer) or [spatie](https://github.com/spatie/laravel-image-optimizer)

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

- install dependencies

```bash
yarn add vue vue-ls vue-tippy@v1 vue2-filters vue-bounty vue-notif vue-clipboard2 vue-awesome vue-touch@next idb-keyval axios dropzone cropperjs keycode babel-preset-es2015-node6 babel-preset-stage-2
# or
npm install vue vue-ls vue-tippy@v1 vue2-filters vue-bounty vue-notif vue-clipboard2 vue-awesome vue-touch@next idb-keyval axios dropzone cropperjs keycode babel-preset-es2015-node6 babel-preset-stage-2 --save
```

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    - if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/)

```js
require('../vendor/MediaManager/js/manager')

new Vue({
    el: '#app'
})
```

<br>

## Features

- [image editor](https://github.com/ctf0/Laravel-Media-Manager/wiki/Image-Editor)
- multi
  + upload
  + move/copy
  + delete
- upload an image from a url
- lazy load image preview
- bulk selection
- change item/s visibility
- dynamically hide [files](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Files-With-Extension)
- dynamically hide [folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Folders)
- toggle between `random/original` names for uploaded files
- download selected ["including bulk selection"](https://github.com/ctf0/Laravel-Media-Manager/wiki/Download-Files-as-a-ZipFile)
- directly copy selected file link
- use the manager
  + [from modal](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-From-A-Modal)
  + [with any wysiwyg editor](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-With-Any-WYSIWYG-Editor)
- auto scroll to selected item when using (left/up, right/down, home, end)
- [lock/unlock](https://github.com/ctf0/Laravel-Media-Manager/wiki/Lock-Files-&-Folder) selected files/folders **"sqLite must be installed"**
- search
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
- autoplay media files ***"if selected filter is audio/video"***
- file name sanitization for
  + upload
  + rename
  + new folder
- disable/enable buttons depend on the usage to avoid noise & keep the user focused
- shortcuts

  |   navigation   |                   button                   |    keyboard   |       click / tap        |          touch          |
  |----------------|--------------------------------------------|---------------|--------------------------|-------------------------|
  |                | upload *(toolbar)*                         | u             | *                        |                         |
  |                | refresh *(toolbar)*                        | r             | * / hold *(clear cache)* |                         |
  |                | move *(toolbar)*                           | m             | *                        | swipe up                |
  |                | image editor *(toolbar)*                   | e             | *                        |                         |
  |                | delete *(toolbar)*                         | d/del         | *                        | swipe down              |
  |                | lock/unlock *(toolbar)*                    | l             | *                        |                         |
  |                | change visibility *(toolbar)*              | v             | *                        |                         |
  |                | (reset) bulk select *(toolbar)*            | b             | *                        |                         |
  |                | (reset) bulk select all *(toolbar)*        | a             | *                        |                         |
  |                | cancel bulk selection                      | esc           |                          |                         |
  |                | cancel search *(toolbar)*                  | esc           | *                        |                         |
  |                | &nbsp;                                     |               |                          |                         |
  |                | toggle *(sidebar)*                         | t             | *                        |                         |
  |                | play/pause media *(sidebar)*               | space         | *                        |                         |
  |                | preview image/pdf/text *(sidebar)*         | space         | *                        |                         |
  |                | &nbsp;                                     |               |                          |                         |
  |                | confirm rename *(modal)*                   | enter         | *                        |                         |
  |                | confirm delete *(modal)*                   | enter         | *                        |                         |
  |                | confirm move *(modal)*                     | enter         | *                        |                         |
  |                | create new folder *(modal)*                | enter         | *                        |                         |
  |                | &nbsp;                                     |               |                          |                         |
  |                | limit bulk select *(files container)*      | shift + click |                          |                         |
  |                | preview image/pdf/text *(files container)* | space         | **                       |                         |
  |                | image editor *(files container)*           |               | hold                     |                         |
  |                | hide *(preview)*                           | space/esc     | *                        |                         |
  | select next    |                                            | right / down  | *                        | swipe left  *(preview)* |
  | select prev    |                                            | left / up     | *                        | swipe right *(preview)* |
  | select first   |                                            | home          |                          |                         |
  | select last    |                                            | end           |                          |                         |
  | open folder    |                                            | enter         | **                       |                         |
  | go to prev dir | folderName *(breadcrumb)*                  | backspace     | *                        | swipe right             |

- events

  |       type      |               event-name              |                   description                    |
  |-----------------|---------------------------------------|--------------------------------------------------|
  | [JS][js]        |                                       |                                                  |
  |                 | modal-show                            | when modal is showen                             |
  |                 | modal-hide                            | when modal is hidden                             |
  |                 | file_selected *(when inside modal)*   | get selected file url                            |
  |                 | folder_selected *(when inside modal)* | get selected folder path                         |
  | [Laravel][lara] |                                       |                                                  |
  |                 | MMFileUploaded($file_path)            | get uploaded file full [path][path]              |
  |                 | [MMFileSaved][event]($file_path)      | get saved(edited/link) image full [path][path]   |
  |                 | MMFileDeleted($file_path, $is_folder) | get deleted file/folder full [path][path]        |
  |                 | MMFileRenamed($old_path, $new_path)   | get renamed file/folder "old & new" [path][path] |
  |                 | MMFileMoved($old_path, $new_path)     | get moved file/folder "old & new" [path][path]   |

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
     * load image preview when item is clicked
     */
    'lazy_load_image_on_click' => false,
];
```

<br>

## Usage

- visit `localhost:8000/media`
- [Wiki](https://github.com/ctf0/Laravel-Media-Manager/wiki)
- [Cacheing Strategy](https://github.com/ctf0/Laravel-Media-Manager/issues/29)
