# Laravel Media Manager

[![Latest Stable Version](https://img.shields.io/packagist/v/ctf0/media-manager.svg?style=for-the-badge)](https://packagist.org/packages/ctf0/media-manager) [![Total Downloads](https://img.shields.io/packagist/dt/ctf0/media-manager.svg?style=for-the-badge)](https://packagist.org/packages/ctf0/media-manager)

The only media manager with this number of features & flexibility.

<p align="center">
    <img src="https://user-images.githubusercontent.com/7388088/34068133-75687998-e23f-11e7-98a8-cd7ded43d209.png">
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
yarn add vue vue-ls vue-multi-ref vue-tippy@v1 vue2-filters vue-bounty vue-notif vue-clipboard2 vue-awesome vue-touch@next axios dropzone keycode babel-preset-es2015-node6 babel-preset-stage-2
# or
npm install vue vue-ls vue-multi-ref vue-tippy@v1 vue2-filters vue-bounty vue-notif vue-clipboard2 vue-awesome vue-touch@next axios dropzone keycode babel-preset-es2015-node6 babel-preset-stage-2 --save
```

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    - if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/)

```js
require('./../vendor/MediaManager/js/manager')

new Vue({
    el: '#app'
})
```

<br>

## Features

- multi
    - upload
    - move
    - delete
- bulk selection
- restrict access to [folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Folder-Restriction)
- dynamically hide [files](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Files-With-Extension)
- dynamically hide [folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Folders)
- toggle between `random names` & `original names` for uploaded files
- download selected ["including bulk selection"](https://github.com/ctf0/Laravel-Media-Manager/wiki/Download-Files-as-a-ZipFile)
- directly copy selected file link
- use the manager
  + [from modal with ease](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-From-A-Modal)
  + [with any wysiwyg editor](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-With-Any-WYSIWYG-Editor)
- auto scroll to selected item when using (left/up, right/down, home, end)
- [lock/unlock selected files/folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Lock-Files-&-Folder) "***sqlite needs to be installed"***
- search
- filter by type
    + folder
    + image
    + audio
    + video
    + text/pdf
- sortBy
    + name "default"
    + size
    + last modified
- items count for
    + all
    + selected
    + search found
- protection against overwriting (files / folders)
- autoplay media files ***"if selected filter is audio/video"***
- file name sanitization for
    + upload
    + rename
    + new folder
- disable/enable buttons depend on the usage to avoid noise & keep the user focused
- shortcuts

    |   navigation   |               button               |   keyboard   | mouse (click) |    touch    |
    |----------------|------------------------------------|--------------|---------------|-------------|
    |                | upload *(toolbar)*                 | u            | *             |             |
    |                | refresh *(toolbar)*                | r            | *             |             |
    |                | move *(toolbar)*                   | m            | *             | swipe up    |
    |                | delete *(toolbar)*                 | d/del        | *             | swipe down  |
    |                | lock/unlock *(toolbar)*            | l            | *             |             |
    |                | bulk select *(toolbar)*            | b            | *             |             |
    |                | bulk select all *(toolbar)*        | a            | *             |             |
    |                | &nbsp;                             |              |               |             |
    |                | confirm rename *(modal)*           | enter        | *             |             |
    |                | confirm delete *(modal)*           | enter        | *             |             |
    |                | confirm move *(modal)*             | enter        | *             |             |
    |                | create new folder *(modal)*        | enter        | *             |             |
    |                | &nbsp;                             |              |               |             |
    |                | toggle *(info panel)*              | t            | *             |             |
    |                | play/pause media *(sidebar)*       | space        | *             |             |
    |                | preview image/pdf/text *(sidebar)* | space        | *             | tap         |
    |                | preview image/pdf/text             | space        | 2x click      | 2x tap      |
    |                | hide image *(light-box)*           | space/esc    | *             |             |
    | select next    |                                    | right / down | *             | swipe left  |
    | select prev    |                                    | left / up    | *             | swipe right |
    | select first   |                                    | home         | *             |             |
    | select last    |                                    | end          | *             |             |
    | open folder    |                                    | enter        | 2x click      | 2x tap      |
    | go to prev dir | folderName *(breadcrumb)*          | backspace    | *             | swipe right |

- events

    |   type  |               event-name              |               description                |
    |---------|---------------------------------------|------------------------------------------|
    | [JS](https://github.com/gocanto/vuemit)      |                                       |                                          |
    |         | modal-show                            | when modal is showen                     |
    |         | modal-hide                            | when modal is hidden                     |
    |         | file_selected *(when inside modal)*   | get selected file url                    |
    | [Laravel](https://laravel.com/docs/5.5/events#manually-registering-events) |                                       |                                          |
    |         | MMFileUploaded($file_path)            | get uploaded file full path              |
    |         | MMFileDeleted($file_path, $is_folder) | get deleted file/folder full path        |
    |         | MMFileRenamed($old_path, $new_path)   | get renamed file/folder "old & new" path |
    |         | MMFileMoved($old_path, $new_path)     | get moved file/folder "old & new" path   |

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
    'sanitized_text' => uniqid(),

    /*
     * display file last modification time as
     * http://carbon.nesbot.com/docs/#api-formatting
     */
    'last_modified_format' => 'toDateString',

    /**
     * hide file extension in files list
     */
    'hide_files_ext' => true
];
```

<br>

## Usage

- visit `localhost:8000/media`
