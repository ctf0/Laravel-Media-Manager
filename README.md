<p align="center">
    <img src="https://user-images.githubusercontent.com/7388088/33029294-e11b7f60-ce20-11e7-91a9-95fb652d3fcd.png">
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

## Features

- multi
    - upload
    - move
    - delete
- bulk selection.
- restrict access to [folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Folder-Restriction)
- dynamically hide [files](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Files-With-Extension)
- dynamically hide [folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Hide-Folders)
- toggle between `random names` & `original names` for uploaded files.
- download selected ***"including bulk selection"***
- directly copy selected file link.
- use manager [from modal with ease](https://github.com/ctf0/Laravel-Media-Manager/wiki/Use-The-Manager-From-A-Modal)
- auto scroll to selected item when using (left/up, right/down, home, end)
- [lock selected files/folders](https://github.com/ctf0/Laravel-Media-Manager/wiki/Lock-Files-&-Folder)
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
- autoplay media files ***"if selected filter is audio/video"***
- file name sanitization for
    + upload
    + rename
    + new folder
- disable/enable buttons depend on the usage to avoid noise & keep the user focused
- shortcuts

    |      navigation     |            button            |   keyboard   | mouse (click) |    touch    |
    |---------------------|------------------------------|--------------|---------------|-------------|
    |                     | upload *(toolbar)*           | u            | *             |             |
    |                     | refresh *(toolbar)*          | r            | *             |             |
    |                     | move *(toolbar)*             | m            | *             | swipe up    |
    |                     | delete *(toolbar)*           | d/del        | *             | swipe down  |
    |                     | lock/unlock *(toolbar)*      | l            | *             |             |
    |                     | bulk select *(toolbar)*      | b            | *             |             |
    |                     | bulk select all *(toolbar)*  | a            | *             |             |
    |                     | &nbsp;                       |              |               |             |
    |                     | confirm rename *(modal)*     | enter        | *             |             |
    |                     | confirm delete *(modal)*     | enter        | *             |             |
    |                     | confirm move *(modal)*       | enter        | *             |             |
    |                     | create new folder *(modal)*  | enter        | *             |             |
    |                     | &nbsp;                       |              |               |             |
    |                     | toggle *(info panel)*        | t            | *             |             |
    |                     | play/pause media *(sidebar)* | space        | *             |             |
    |                     | preview image *(sidebar)*    | space        | *             | 2x tap      |
    |                     | hide image *(light-box)*     | space/esc    | *             |             |
    | select next         |                              | right / down | *             | swipe left  |
    | select prev         |                              | left / up    | *             | swipe right |
    | select first        |                              | home         | *             |             |
    | select last         |                              | end          | *             |             |
    | open folder         |                              | enter        | 2x click      | 2x tap      |
    | go back to prev dir | folderName *(breadcrumb)*    | backspace    | *             | swipe right |

- events

    |   type  |               event-name              |               description                |
    |---------|---------------------------------------|------------------------------------------|
    | JS      |                                       |                                          |
    |         | modal-show                            | when modal is showen                     |
    |         | modal-hide                            | when modal is hidden                     |
    |         | no-files-show                         | when no files msg is showen              |
    |         | no-files-hide                         | when no files msg is hidden              |
    |         | loading-files-show                    | when loading files is hidden             |
    |         | loading-files-hide                    | when loading files is hidden             |
    |         | ajax-error-show                       | when ajax call fails                     |
    |         | file_selected *(when using modal)*    | get selected file url                    |
    | Laravel |                                       |                                          |
    |         | MMFileUploaded($file_path)            | get uploaded file full path              |
    |         | MMFileDeleted($file_path, $is_folder) | get deleted file/folder full path        |
    |         | MMFileRenamed($old_path, $new_path)   | get renamed file/folder "old & new" path |
    |         | MMFileMoved($old_path, $new_path)     | get moved file/folder "old & new" path   |

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

## Usage

- For everyone that hate ***npm*** & possibly the whole js ecosystem **"YOU ARE NOT ALONE"** but maintaining a seperate dist copy is a tedious job for me, so if you need a help installing it, here are some steps to get you going
    + install npm https://www.npmjs.com/get-npm
    + (optional) install yarn https://yarnpkg.com/lang/en/docs/install/
    + follow the steps below :clap: :muscle: :dancers:

---

- install dependencies

```bash
yarn add vue axios dropzone keycode vue-ls vue-tippy vue2-filters vuemit vue-notif vue-clipboard2 vue-touch@next babel-preset-es2015-node6 babel-preset-stage-2
# or
npm install vue axios dropzone keycode vue-ls vue-tippy vue2-filters vuemit vue-notif vue-clipboard2 vue-touch@next babel-preset-es2015-node6 babel-preset-stage-2 --save
```

- for styling we use ***bulma***

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    + if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/)

```js
require('./../vendor/MediaManager/js/manager')

new Vue({
    el: '#app'
})
```

- now visit `localhost:8000/media`
