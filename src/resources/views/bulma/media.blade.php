<!DOCTYPE html>
<html>
<head>
    <title>Media Manager</title>
    {{-- FW --}}
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    {{-- bulma --}}
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.5.0/css/bulma.min.css">
    {{-- Main styles --}}
    <link rel="stylesheet" href="{{ mix('assets/vendor/MediaManager/style.css') }}"/>

    {{-- js --}}
    <script src="//code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="//cdn.jsdelivr.net/jquery.scrollto/2.1.2/jquery.scrollTo.min.js"></script>
    {{-- ziggy --}}
    @routes
</head>
<body>
    <div id="app" v-cloak>
        {{-- notifications --}}
        <div class="notif-container">
            <my-notification></my-notification>
        </div>

        <div class="container is-fluid is-marginless">
            <div class="columns">
                {{-- media manager --}}
                <div class="column">
                    {{-- top toolbar --}}
                    <nav id="toolbar" class="level">
                        {{-- left toolbar --}}
                        <div class="level-left">
                            {{-- manager --}}
                            <div class="level-item">
                                <div class="field is-grouped">
                                    <div class="control">
                                        <div class="field has-addons">
                                            <div class="control">
                                                <button class="button" id="upload" v-tippy title="u" data-arrow="true">
                                                    <span class="icon is-small"><i class="fa fa-cloud-upload"></i></span>
                                                    <span>{{ trans('MediaManager::messages.upload') }}</span>
                                                </button>
                                            </div>
                                            <div class="control">
                                                <button class="button" id="new_folder">
                                                    <span class="icon is-small"><i class="fa fa-folder"></i></span>
                                                    <span>{{ trans('MediaManager::messages.add_folder') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="control">
                                        <button class="button is-light" id="refresh" v-tippy title="r" data-arrow="true">
                                            <span class="icon is-small"><i class="fa fa-refresh"></i></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <div class="field has-addons">
                                            <div class="control">
                                                <button class="button is-info" id="move" v-tippy title="m" data-arrow="true">
                                                    <span class="icon is-small"><i class="fa fa-share"></i></span>
                                                    <span>{{ trans('MediaManager::messages.move') }}</span>
                                                </button>
                                            </div>
                                            <div class="control">
                                                <button class="button is-info" id="rename">
                                                    <span class="icon is-small"><i class="fa fa-i-cursor"></i></span>
                                                    <span>{{ trans('MediaManager::messages.rename') }}</span>
                                                </button>
                                            </div>
                                            <div class="control">
                                                <button class="button is-info" id="delete" v-tippy title="d / del" data-arrow="true">
                                                    <span class="icon is-small"><i class="fa fa-trash"></i></span>
                                                    <span>{{ trans('MediaManager::messages.delete') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- right toolbar --}}
                        <div class="level-right">
                            <div class="level-item">
                                <div class="field is-grouped">
                                    {{-- multi --}}
                                    <div class="control">
                                        <div class="field">
                                            <div class="control">
                                                <button id="blk_slct_all" class="button" v-tippy title="a" data-arrow="true">
                                                    <span class="icon is-small"><i class="fa fa-plus"></i></span>
                                                    <span>Select All</span>
                                                </button>
                                            </div>
                                            <div class="control">
                                                <button id="blk_slct" class="button" :disabled="!allItemsCount" v-tippy title="b" data-arrow="true">
                                                    <span class="icon is-small"><i class="fa fa-puzzle-piece"></i></span>
                                                    <span>{{ trans('MediaManager::messages.bulk_select') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <template v-if="allItemsCount">
                                        {{-- filter by --}}
                                        <div class="control">
                                            <div class="field has-addons">
                                                <div class="control">
                                                    <button @click="showFilesOfType('image')"
                                                        v-tippy title="Filter By Image" data-arrow="true"
                                                        class="button"
                                                        :class="{'is-info': filterNameIs('image')}"
                                                        :disabled="!btnFilter('image')">
                                                        <span class="icon is-small"><i class="fa fa-image"></i></span>
                                                    </button>
                                                </div>
                                                <div class="control">
                                                    <button @click="showFilesOfType('video')"
                                                        v-tippy title="Filter By Video" data-arrow="true"
                                                        class="button"
                                                        :class="{'is-info': filterNameIs('video')}"
                                                        :disabled="!btnFilter('video')">
                                                        <span class="icon is-small"><i class="fa fa-video-camera"></i></span>
                                                    </button>
                                                </div>
                                                <div class="control">
                                                    <button @click="showFilesOfType('audio')"
                                                        v-tippy title="Filter By Audio" data-arrow="true"
                                                        class="button"
                                                        :class="{'is-info': filterNameIs('audio')}"
                                                        :disabled="!btnFilter('audio')">
                                                        <span class="icon is-small"><i class="fa fa-music"></i></span>
                                                    </button>
                                                </div>
                                                <div class="control">
                                                    <button @click="showFilesOfType('folder')"
                                                        v-tippy title="Filter By Folder" data-arrow="true"
                                                        class="button"
                                                        :class="{'is-info': filterNameIs('folder')}"
                                                        :disabled="!btnFilter('folder')">
                                                        <span class="icon is-small"><i class="fa fa-folder"></i></span>
                                                    </button>
                                                </div>
                                                <div class="control">
                                                    <button @click="showFilesOfType('text')"
                                                        v-tippy title="Filter By Text" data-arrow="true"
                                                        class="button"
                                                        :class="{'is-info': filterNameIs('text')}"
                                                        :disabled="!btnFilter('text')">
                                                        <span class="icon is-small"><i class="fa fa-file-text"></i></span>
                                                    </button>
                                                </div>

                                                <div class="control">
                                                    <button @click="showFilesOfType('all')"
                                                        v-tippy title="Clear Filter" data-arrow="true"
                                                        class="button"
                                                        :class="{'is-danger': btnFilter('all')}"
                                                        :disabled="!btnFilter('all')">
                                                        <span class="icon is-small"><i class="fa fa-times"></i></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- showBy --}}
                                        <div class="control">
                                            <div class="select">
                                                <select v-model="showBy">
                                                    <option value="null" disabled>Sort By</option>
                                                    <option value="clear">Non</option>
                                                    <option value="size">Size</option>
                                                    <option value="last_modified">Last Modified</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- search --}}
                                        <div class="control">
                                            <div class="field has-addons">
                                                <p class="control">
                                                    <input class="input" type="text" placeholder="Find ..." v-model="searchFor">
                                                </p>
                                                <p class="control">
                                                    <button class="button is-black" :disabled="!searchFor" @click="searchFor = ''" v-tippy title="Clear Search" data-arrow="true">
                                                        <span class="icon is-small"><i class="fa fa-times"></i></span>
                                                    </button>
                                                </p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </nav>

                    {{-- upload --}}
                    <div class="field">
                        <form class="dz" id="new-upload" action="{{ route('media.upload') }}">
                            <div class="dz-message title is-4">
                                {{ trans('MediaManager::messages.drag_drop_info') }}
                            </div>
                            {{ csrf_field() }}
                            <input type="hidden" name="upload_path" :value="files.path ? files.path : '/'">
                        </form>

                        <div id="uploadPreview" class="dropzone-previews"></div>

                        <div id="uploadProgress" class="progress">
                            <div class="progress-bar is-success progress-bar-striped active"></div>
                        </div>
                    </div>

                    {{-- files area --}}
                    <div id="content">
                        {{-- directories breadCrumb --}}
                        <div class="breadcrumb-container">
                            <ol class="breadcrumb">
                                <li @click="goToFolder(0)">
                                    <span class="arrow"></span>
                                    <a class="p-l-0"><strong>{{ trans('MediaManager::messages.library') }}</strong></a>
                                </li>

                                <template v-for="(folder,index) in folders">
                                    <li @click="goToFolder(index+1)">
                                        <span class="arrow"></span>
                                        <p v-if="lastItem(folder, folders)">@{{ folder }}</p>
                                        <a v-else v-tippy title="backspace" data-arrow="true">@{{ folder }}</a>
                                    </li>
                                </template>
                            </ol>

                            <div class="toggle button" @click="toggleInfo()" v-tippy title="t" data-arrow="true">
                                <span>Close</span>
                                <span class="icon"><i class="fa fa-angle-double-right"></i></span>
                            </div>
                        </div>

                        <div class="flex">
                            {{-- files box --}}
                            <div id="left">
                                <ul id="files" class="tile">
                                    <li v-for="(file,index) in orderBy(filterBy(allFiles, searchFor, 'name'), showBy, -1)"
                                        @click="setSelected(file)" @dblclick="openFolder(file)">
                                        <div class="file_link" :class="{'bulk-selected': IsInBulkList(file)}" :data-folder="file.name" :data-index="index">
                                            <div class="link_icon">
                                                <template v-if="fileTypeIs(file, 'image')">
                                                    <div class="image" :style="{ 'background-image': 'url(' + encodeURI(file.path) + ')' }"></div>
                                                </template>

                                                <span class="icon is-large" v-else>
                                                    <i v-if="fileTypeIs(file, 'folder')" class="fa fa-folder"></i>
                                                    <i v-if="fileTypeIs(file, 'video')" class="fa fa-video-camera"></i>
                                                    <i v-if="fileTypeIs(file, 'audio')" class="fa fa-music"></i>
                                                    <i v-if="fileTypeIs(file, 'text')" class="fa fa-file-text"></i>
                                                </span>
                                            </div>
                                            <div class="details">
                                                <h4>@{{ file.name }}</h4>
                                                <small>
                                                    <template v-if="fileTypeIs(file, 'folder')">
                                                        @{{ file.items }} item(s)
                                                    </template>
                                                    <template v-else>
                                                        <span class="file_size">@{{ file.size }}</span>
                                                    </template>
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                </ul>

                                {{-- loading data from server --}}
                                <div id="file_loader">
                                    <p>
                                        <span class="control is-loading icon is-medium"></span>
                                        {{ trans('MediaManager::messages.loading') }}
                                    </p>
                                </div>

                                {{-- no files --}}
                                <div id="no_files">
                                    <h3>
                                        <span class="icon"><i class="fa fa-meh-o"></i></span>
                                        <span>{{ trans('MediaManager::messages.no_files_in_folder') }}</span>
                                    </h3>
                                </div>
                            </div>

                            {{-- info box --}}
                            <div id="right">
                                <div class="right_none_selected" v-if="!selectedFile">
                                    <i class="fa fa-mouse-pointer"></i>
                                    <p>{{ trans('MediaManager::messages.nothing_selected') }}</p>
                                </div>

                                <div class="right_details">
                                    {{-- img / icon --}}
                                    <template v-if="selectedFile">
                                        <div class="detail_img">
                                            <template v-if="selectedFileIs('image')">
                                                <lightbox :src="selectedFile.path" class="quickView">
                                                    <img :src="selectedFile.path" v-tippy title="space" data-arrow="true"/>
                                                </lightbox>
                                            </template>
                                            <template v-if="selectedFileIs('video')">
                                                <video controls class="video player" :key="selectedFile.name" v-tippy title="space" data-arrow="true">
                                                    <source :src="selectedFile.path" type="video/mp4">
                                                    <source :src="selectedFile.path" type="video/ogg">
                                                    <source :src="selectedFile.path" type="video/webm">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </template>
                                            <template v-if="selectedFileIs('audio')">
                                                <audio controls class="audio player" :key="selectedFile.name" v-tippy title="space" data-arrow="true">
                                                    <source :src="selectedFile.path" type="audio/ogg">
                                                    <source :src="selectedFile.path" type="audio/mpeg">
                                                    Your browser does not support the audio element.
                                                </audio>
                                            </template>
                                            <i v-if="selectedFileIs('folder')" class="fa fa-folder"></i>
                                            <i v-if="selectedFileIs('text')" class="fa fa-file-text-o"></i>
                                        </div>

                                        {{-- data --}}
                                        <div class="detail_info">
                                            <div>
                                                <h4>Title: <span>@{{ selectedFile.name }}</span></h4>
                                                <h4>Type: <span>@{{ selectedFile.type }}</span></h4>
                                                <template v-if="!selectedFileIs('folder')">
                                                    <h4>Size: <span>@{{ selectedFile.size }}</span></h4>
                                                    <h4>Public URL: <a :href="selectedFile.path" target="_blank">Click Here</a></h4>
                                                </template>
                                                <template v-else>
                                                    <h4>items: <span>@{{ selectedFile.items }} Item(s)</span></h4>
                                                </template>
                                                <h4>Last Modified: <span>@{{ selectedFile.last_modified }}</span></h4>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- items count --}}
                                <div class="count" v-if="allItemsCount">
                                    <p class="title is-marginless" v-if="bulkItemsCount">@{{ bulkItemsCount }} Selected</p>
                                    <p class="title is-marginless" v-if="searchItemsCount !== null && searchItemsCount >= 0">@{{ searchItemsCount }} Found</p>
                                    <p class="title is-marginless">@{{ allItemsCount }} Total</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- modals --}}
                    <div class="modal fade" id="new_folder_modal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header is-info">
                                    <button class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">
                                        <span class="icon"><i class="fa fa-folder"></i> </span>
                                        <span>{{ trans('MediaManager::messages.add_new_folder') }}</span>
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <input id="new_folder_name" class="input" type="text" placeholder="{{ trans('MediaManager::messages.new_folder_name') }}">
                                </div>
                                <div class="modal-footer">
                                    <button class="button" data-dismiss="modal">{{ trans('MediaManager::messages.cancel') }}</button>
                                    <button class="button is-info" id="new_folder_submit">{{ trans('MediaManager::messages.create_new_folder') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="move_file_modal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header is-warning">
                                    <button class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">
                                        <span class="icon"><i class="fa fa-share"></i></span>
                                        <span>{{ trans('MediaManager::messages.move_file_folder') }}</span>
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <h4 class="title">{{ trans('MediaManager::messages.destination_folder') }}</h4>
                                    <div class="control has-icons-left">
                                        <span class="select is-fullwidth">
                                            <select id="move_folder_dropdown">
                                                <option v-if="folders.length" value="../">../</option>
                                                <option v-for="dir in directories" v-if="filterDir(dir)" :value="dir">@{{ dir }}</option>
                                            </select>
                                        </span>
                                        <span class="icon is-small is-left">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="button" data-dismiss="modal">{{ trans('MediaManager::messages.cancel') }}</button>
                                    <button class="button is-warning" id="move_btn">{{ trans('MediaManager::messages.move') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="rename_file_modal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header is-warning">
                                    <button class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">
                                        <span class="icon"><i class="fa fa-i-cursor"></i></span>
                                        <span> {{ trans('MediaManager::messages.rename_file_folder') }}</span>
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <h4 class="title">{{ trans('MediaManager::messages.new_file_folder') }}</h4>
                                    <input id="new_filename" class="input" type="text" :value="fileName(selectedFile.name)" v-if="selectedFile">
                                </div>
                                <div class="modal-footer">
                                    <button class="button" data-dismiss="modal">{{ trans('MediaManager::messages.cancel') }}</button>
                                    <button class="button is-warning" id="rename_btn">{{ trans('MediaManager::messages.rename') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="confirm_delete_modal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header is-danger">
                                    <button class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">
                                        <span class="icon"><i class="fa fa-warning"></i> </span>
                                        <span>{{ trans('MediaManager::messages.are_you_sure') }}</span>
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <h4 class="title">{{ trans('MediaManager::messages.are_you_sure_delete') }}</h4>
                                    <template v-if="bulkItemsCount">
                                        <table class="table" v-if="bulkItemsCount <= 8">
                                            <tr class="confirm_delete" v-for="item in bulkList">
                                                <td class="p-r-0" width="1%">
                                                    <span class="icon">
                                                        <i v-if="fileTypeIs(item, 'folder')" class="fa fa-folder"></i>
                                                        <i v-if="fileTypeIs(item, 'image')" class="fa fa-image"></i>
                                                        <i v-if="fileTypeIs(item, 'video')" class="fa fa-video-camera"></i>
                                                        <i v-if="fileTypeIs(item, 'audio')" class="fa fa-music"></i>
                                                        <i v-if="fileTypeIs(item, 'text')" class="fa fa-file-text"></i>
                                                    </span>
                                                </td>
                                                <td v-if="item.items">@{{ item.name }} "@{{ item.items }} item(s)"</td>
                                                <td v-else>@{{ item.name }}</td>
                                            </tr>
                                        </table>
                                        <template v-else>
                                            <p class="m-l-50">
                                                <span class="icon is-medium confirm_delete_icon"><i class="fa fa-archive"></i></span>
                                                <span class="title is-4 confirm_delete">{{ trans('MediaManager::messages.too_many_files') }}</span>
                                            </p>
                                        </template>
                                    </template>

                                    <template v-else>
                                        <table class="table" v-if="selectedFile">
                                            <tr>
                                                <td class="p-r-0 confirm_delete_icon" width="1%">
                                                    <span class="icon">
                                                        <i v-if="selectedFileIs('folder')" class="fa fa-folder"></i>
                                                        <i v-if="selectedFileIs('image')" class="fa fa-image"></i>
                                                        <i v-if="selectedFileIs('video')" class="fa fa-video-camera"></i>
                                                        <i v-if="selectedFileIs('audio')" class="fa fa-music"></i>
                                                        <i v-if="selectedFileIs('text')" class="fa fa-file-text"></i>
                                                    </span>
                                                </td>
                                                <td class="confirm_delete"></td>
                                            </tr>
                                        </table>
                                    </template>
                                    <h5 class="folder_warning">
                                        <span class="icon"><i class="fa fa-warning"></i></span>
                                        <span>{{ trans('MediaManager::messages.delete_folder_question') }}</span>
                                    </h5>
                                </div>
                                <div class="modal-footer">
                                    <button class="button" data-dismiss="modal">{{ trans('MediaManager::messages.cancel') }}</button>
                                    <button class="button is-danger" id="confirm_delete">{{ trans('MediaManager::messages.delete_confirm') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- footer --}}
    <script>
        const warningClass = 'is-warning'
        const errorClass = 'is-danger'
    </script>
    <script src="{{ mix("assets/vendor/MediaManager/script.js") }}"></script>
</body>
</html>