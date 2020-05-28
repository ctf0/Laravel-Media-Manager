<h1 align="center">
    Laravel Media Manager
    <br>
    <a href="https://packagist.org/packages/laravel/laravel"><img src="https://img.shields.io/badge/Laravel-v6+-red" alt="Browser Status"/></a>
    <a href="https://packagist.org/packages/ctf0/media-manager"><img src="https://img.shields.io/packagist/v/ctf0/media-manager.svg" alt="Latest Stable Version"/></a>
    <a href="https://packagist.org/packages/ctf0/media-manager"><img src="https://img.shields.io/packagist/dt/ctf0/media-manager.svg" alt="Total Downloads"/></a>
    <br>
    <img src="https://badges.herokuapp.com/browsers?firefox=61&amp;microsoftedge=17&amp;googlechrome=51&amp;safari=10&amp;iexplore=!?" alt="Browser Status"/>
</h1>

<p align="center">
    <img alt="main" src="https://user-images.githubusercontent.com/7388088/70385131-c0937e80-1993-11ea-9513-cb5c90f4281b.png"/>
    <img alt="card" src="https://user-images.githubusercontent.com/7388088/75881844-8fa6d680-5e28-11ea-8a38-09bbce94d68e.png"/>
</p>

- [Installation](#Installation)
- [Config](#Config)
- [Features](#Features)
- [Events](#Events)
- [Usage](#Usage)

<br>

## Installation

- `composer require ctf0/media-manager`

- publish the package assets with

    `php artisan vendor:publish --provider="ctf0\MediaManager\MediaManagerServiceProvider"`

- after installation, run `php artisan lmm:setup` to add
    + package routes to `routes/web.php`
    + package assets compiling to `webpack.mix.js`

- for [lock/unlock](https://github.com/ctf0/Laravel-Media-Manager/wiki/Lock-Files-&-Folder) item/s we use a db "sqlite" but if you prefer to use something else you should run the migration 
    
    ```bash
    php artisan migrate
    ```

- [install dependencies](https://github.com/ctf0/Laravel-Media-Manager/wiki/Packages-In-Use)

    ```bash
    yarn add vue vue-ls vue-infinite-loading vue-image-compare2 vue-tippy@v2 vue2-filters vue-input-autowidth vue-notif vue-clipboard2 vue-awesome@v2 vue-touch@next vue-focuspoint-component axios dropzone cropperjs keycode lottie-web plyr fuse.js music-metadata-browser idb-keyval annyang
    yarn add worker-loader --dev
    ```

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    + if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/).

    ```js
    // app.js
    window.Vue = require('vue')

    require('../assets/vendor/MediaManager/js/manager')

    new Vue({
        el: '#app'
    })
    ```

<br>

## Config

- [**config/mediaManager.php**](https://github.com/ctf0/Laravel-Media-Manager/blob/master/src/config/mediaManager.php)

<br>

## Features

- [image editor](https://github.com/ctf0/Laravel-Media-Manager/wiki/Image-Editor)
- multi
    + upload
    + move/copy
    + delete
- upload by either
    + using the upload panel
    + drag & drop anywhere
    + click & hold on an empty area **"items container"**
    + from a url
- [preview files before uploading](https://github.com/ctf0/Laravel-Media-Manager/wiki/Preview-Files-Before-Uploading)
- toggle between `random/original` names for uploaded files
- [asynchronous Updates](https://github.com/ctf0/Laravel-Media-Manager/wiki/Async-Update-The-Manager)
- bulk selection
- bookmark visited directories for quicker navigation
- change item/s visibility
- update the page url on navigation
- show audio files info **"artist, album, year, etc.."**
- dynamically hide [files](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Files-With-Extension) / [folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Folders)
- [restrict access to path](https://github.com/ctf0/Laravel-Media-Manager/wiki/Restrict-Access-To-Path)
- download selected ["including bulk selection"](https://github.com/ctf0/Laravel-Media-Manager/wiki/Download-Files-as-a-ZipFile)
- directly copy selected file link
- use the manager
    + [from modal](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-From-A-Modal)
    + [with any wysiwyg editor](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-With-Any-WYSIWYG-Editor)
- auto scroll to selected item using **"left, up, right, down, home, end"**
- [lock/unlock](https://github.com/ctf0/Laravel-Media-Manager/wiki/Lock-Files-&-Folder) item/s.
- search in the current directory **or** globally through the entire collection.
- filter by
    + folder
    + image
    + audio
    + video
    + text/pdf
    + application/archive
    + locked items
    + selected items
- sort by
    + name
    + size
    + last modified
- items count for
    + all
    + selected
    + search found
- contents ratio bar
- protection against overwriting (files/folders)
- file name sanitization for
    + upload
    + rename
    + new folder
- disable/enable buttons depend on the usage to avoid noise & keep the user focused
- shortcuts / gestures
    + if no more **rows** available, pressing `down` will go to the last item in the list **"same as native file manager"**.
    + when viewing a `audio/video` file in the preview card, pressing `space` will **play/pause** the item instead of closing the modal.
    + dbl click/tap
        + any file of type `audio/video` when sidebar is hidden, will open it in the preview card **"same as images"**.
        + any file of type `application/archive` will download it.
    + all the **left/right** gestures have their counterparts available as well.
    + pressing `esc` while using the ***image editor*** wont close the modal but you can ***dbl click/tap*** the `modal background` to do so. **"to avoid accidentally canceling your changes"**.

>\- the info sidebar is only available on big screens **"> 1023px"**.<br>
>\- to stop interfering with other `keydown` events you can toggle the manager listener through<br>
>`EventHub.fire('disable-global-keys', true/false)`.

<br>

| navigation           | button                                              | keyboard         | click / tap                  | touch                           |
|----------------------|-----------------------------------------------------|------------------|------------------------------|---------------------------------|
|                      | toggle upload panel *(toolbar)*                     | u                |                              |                                 |
|                      | refresh *(toolbar)*                                 | r                | hold *"clear cache"*         | pinch in *(items container)*    |
|                      | [move/show movable list][movable] *(toolbar)*       | m / p            |                              |                                 |
|                      | image editor *(toolbar)*                            | e                |                              |                                 |
|                      | delete *(toolbar)*                                  | d / del          |                              |                                 |
|                      | lock/unlock *(toolbar)*                             | l                | hold *"anything but images"* |                                 |
|                      | change visibility *(toolbar)*                       | v                |                              |                                 |
|                      | toggle bulk selection *(toolbar)*                   | b                |                              |                                 |
|                      | (reset) bulk select all *(toolbar)*                 | a                |                              |                                 |
|                      | add to movable list *(shopping cart)*               | c / x            | *                            |                                 |
|                      | [move/show movable list][movable] *(shopping cart)* |                  | **                           |                                 |
|                      | clear movable list *(shopping cart)*                |                  | hold                         |                                 |
|                      | toggle sidebar *(path bar)*                         | t                | *                            | swipe left/right *(sidebar)*    |
|                      | confirm *(modal)*                                   | enter            |                              |                                 |
|                      | toggle preview image/pdf/text *(item)*              | space            | **                           |                                 |
|                      | play/pause media *(item)*                           | space            | **                           |                                 |
|                      | hide (modal / upload-panel)                         | esc              |                              |                                 |
|                      | reset (search / bulk selection / filter / sorting)  | esc              |                              |                                 |
|                      | reset upload [showPreview][showPreview]             | esc              |                              |                                 |
|                      | confirm upload [showPreview][showPreview]           | enter            |                              |                                 |
|                      | &nbsp;                                              |                  |                              |                                 |
|                      | add to movable list *(item)*                        |                  |                              | swipe up                        |
|                      | delete *(item)*                                     |                  |                              | swipe down                      |
|                      | rename *(item)*                                     |                  |                              | swipe left                      |
|                      | image editor *(item)*                               |                  | hold                         |                                 |
|                      | current ++ selected *(item)*                        | shift + click    |                              |                                 |
|                      | current + selected *(item)*                         | alt/meta + click |                              |                                 |
|                      | create new folder                                   |                  | ** *(items container)*       |                                 |
|                      | &nbsp;                                              |                  |                              |                                 |
| go to next *"item"*  |                                                     | right            | *                            | swipe left  *(preview)*         |
| go to prev *"item"*  |                                                     | left             | *                            | swipe right *(preview)*         |
| go to first *"item"* |                                                     | home             |                              |                                 |
| go to last *"item"*  |                                                     | end              |                              |                                 |
| go to next *"row"*   |                                                     | down             |                              | swipe up *(preview)*            |
| go to prev *"row"*   |                                                     | up               |                              | swipe down *(preview)*          |
| open folder          |                                                     | enter            | **                           |                                 |
| go to prev *"dir"*   | folderName *(path bar)*                             | backspace        | *                            | swipe right *(items container)* |

<br>

## Events

| type            | event-name                                         | description                                                                |
|-----------------|----------------------------------------------------|----------------------------------------------------------------------------|
| [JS][js]        |                                                    |                                                                            |
|                 | modal-show                                         | when modal is shown                                                        |
|                 | modal-hide                                         | when modal is hidden                                                       |
|                 | file_selected *([when inside modal][modal])*       | get selected file url                                                      |
|                 | multi_file_selected *([when inside modal][modal])* | get bulk selected files urls                                               |
|                 | folder_selected *([when inside modal][modal])*     | get selected folder path                                                   |
| [Laravel][lara] |                                                    |                                                                            |
|                 | MMFileUploaded($file_path, $mime_type, $options)   | get uploaded file storage path, mime type, [custom options][customOptions] |
|                 | [MMFileSaved][event]($file_path, $mime_type)       | get saved (edited/link) image full storage path, mime type                 |
|                 | MMFileDeleted($file_path, $is_folder)              | get deleted file/folder storage path, if removed item is a folder          |
|                 | MMFileRenamed($old_path, $new_path)                | get renamed file/folder "old & new" storage path                           |
|                 | MMFileMoved($old_path, $new_path)                  | get moved file/folder "old & new" storage path                             |

[js]: https://github.com/gocanto/vuemit
[lara]: https://laravel.com/docs/master/events#manually-registering-events
[event]: https://github.com/ctf0/Laravel-Media-Manager/wiki/Image-Editor#optimize-edited-images-on-save
[modal]: https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-From-A-Modal
[showPreview]: https://github.com/ctf0/Laravel-Media-Manager/blob/master/src/config/mediaManager.php#L126
[customOptions]: https://github.com/ctf0/Laravel-Media-Manager/wiki/Preview-Files-Before-Uploading#-send-custom-options-with-uploaded-files
[movable]: https://github.com/ctf0/Laravel-Media-Manager/wiki/Movable-List

<br>

## Usage

> [Wiki](https://github.com/ctf0/Laravel-Media-Manager/wiki)<br>
> [Demo](https://github.com/ctf0/demos/tree/media-manager)

- visit `localhost:8000/media`
