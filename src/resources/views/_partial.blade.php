{{-- styles --}}
<link rel="stylesheet" href="{{ asset('assets/vendor/MediaManager/style.css') }}"/>

{{-- component --}}
<media-manager inline-template
    files-route="{{ route('media.files') }}"
    dirs-route="{{ route('media.directories') }}"
    :hide-ext="{{ config('mediaManager.hide_ext') ? 'true' : 'false' }}"
    restrict-path="{{ isset($path) ? $path : null }}">
    <div>

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
                                    <button class="button" id="upload" v-tippy="{arrow: true}" title="u">
                                        <span class="icon is-small"><i class="fa fa-cloud-upload"></i></span>
                                        <span>{{ trans('MediaManager::messages.upload') }}</span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button class="button" id="new_folder" @click="toggleModal('#new_folder_modal')">
                                        <span class="icon is-small"><i class="fa fa-folder"></i></span>
                                        <span>{{ trans('MediaManager::messages.add_folder') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="control">
                            <button class="button is-light" id="refresh"
                                v-tippy="{arrow: true}" title="r"
                                @click="getFiles(folders)">
                                <span class="icon is-small"><i class="fa fa-refresh"></i></span>
                            </button>
                        </div>

                        <div class="control">
                            <div class="field has-addons">
                                <div class="control">
                                    <button class="button is-link" id="move"
                                        v-tippy="{arrow: true}" title="m"
                                        @click="toggleModal('#move_file_modal')">
                                        <span class="icon is-small"><i class="fa fa-share"></i></span>
                                        <span>{{ trans('MediaManager::messages.move') }}</span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button class="button is-link" id="rename" @click="toggleModal('#rename_file_modal')">
                                        <span class="icon is-small"><i class="fa fa-i-cursor"></i></span>
                                        <span>{{ trans('MediaManager::messages.rename') }}</span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button class="button is-link" id="delete"
                                        v-tippy="{arrow: true}" title="d / del"
                                        @click="toggleModal('#confirm_delete_modal')">
                                        <span class="icon is-small"><i class="fa fa-trash"></i></span>
                                        <span>{{ trans('MediaManager::messages.delete') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ====================================================================== --}}

            {{-- right toolbar --}}
            <div class="level-right is-hidden-touch">
                <div class="level-item">
                    <div class="field is-grouped">
                        {{-- multi --}}
                        <div class="control">
                            <div class="field">
                                <div class="control">
                                    <button id="blk_slct_all" class="button" v-tippy="{arrow: true}" title="a">
                                        <span class="icon is-small"><i class="fa fa-plus"></i></span>
                                        <span>Select All</span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button id="blk_slct" class="button" :disabled="!allItemsCount" v-tippy="{arrow: true}" title="b">
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
                                            v-tippy="{arrow: true}" title="Filter By Image"
                                            class="button"
                                            :class="{'is-link': filterNameIs('image')}"
                                            :disabled="!btnFilter('image')">
                                            <span class="icon is-small"><i class="fa fa-image"></i></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('video')"
                                            v-tippy="{arrow: true}" title="Filter By Video"
                                            class="button"
                                            :class="{'is-link': filterNameIs('video')}"
                                            :disabled="!btnFilter('video')">
                                            <span class="icon is-small"><i class="fa fa-video-camera"></i></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('audio')"
                                            v-tippy="{arrow: true}" title="Filter By Audio"
                                            class="button"
                                            :class="{'is-link': filterNameIs('audio')}"
                                            :disabled="!btnFilter('audio')">
                                            <span class="icon is-small"><i class="fa fa-music"></i></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('folder')"
                                            v-tippy="{arrow: true}" title="Filter By Folder"
                                            class="button"
                                            :class="{'is-link': filterNameIs('folder')}"
                                            :disabled="!btnFilter('folder')">
                                            <span class="icon is-small"><i class="fa fa-folder"></i></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('text')"
                                            v-tippy="{arrow: true}" title="Filter By Text"
                                            class="button"
                                            :class="{'is-link': filterNameIs('text')}"
                                            :disabled="!btnFilter('text')">
                                            <span class="icon is-small"><i class="fa fa-file-text"></i></span>
                                        </button>
                                    </div>

                                    <div class="control">
                                        <button @click="showFilesOfType('all')"
                                            v-tippy="{arrow: true}" title="Clear Filter"
                                            class="button"
                                            :class="{'is-danger': btnFilter('all')}"
                                            :disabled="!btnFilter('all')">
                                            <span class="icon is-small"><i class="fa fa-times"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- showBy --}}
                            <div class="control has-icons-left">
                                <div class="select">
                                    <select v-model="showBy">
                                        <option disabled value="undefined">Sort By</option>
                                        <option value="clear">Non</option>
                                        <option value="size">Size</option>
                                        <option value="last_modified">Last Modified</option>
                                    </select>
                                </div>
                                <div class="icon is-small is-left">
                                    <i class="fa fa-bell-o"></i>
                                </div>
                            </div>

                            {{-- search --}}
                            <div class="control">
                                <div class="field has-addons">
                                    <p class="control has-icons-left">
                                        <input class="input" type="text" placeholder="Find" v-model="searchFor">
                                        <span class="icon is-small is-left">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </p>
                                    <p class="control">
                                        <button class="button is-black" :disabled="!searchFor"
                                            v-tippy="{arrow: true}" title="Clear Search"
                                            @click="resetInput('searchFor')" >
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

        {{-- ====================================================================== --}}

        {{-- upload --}}
        <div class="field is-marginless">
            <div id="dz">
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
        </div>

        {{-- ====================================================================== --}}

        {{-- files area --}}
        <div id="content">
            {{-- directories breadCrumb --}}
            <div class="breadcrumb-container level is-mobile">
                <div class="level-left">
                    <ol class="breadcrumb">
                        <li v-if="!checkForRestriction()">
                            <a v-if="folders.length > 0 && !isBulkSelecting()" class="p-l-0" @click="goToFolder(0)">
                                {{ trans('MediaManager::messages.library') }}
                            </a>
                            <p v-else class="p-l-0">{{ trans('MediaManager::messages.library') }}</p>
                        </li>

                        <template v-for="(folder,index) in folders">
                            <li @click="goToFolder(index+1)">
                                <p v-if="isLastItem(folder, folders)">@{{ folder }}</p>
                                <a v-else v-tippy="{arrow: true}" title="backspace">@{{ folder }}</a>
                            </li>
                        </template>
                    </ol>
                </div>

                <div class="level-right is-hidden-touch">
                    <div class="toggle" @click="toggleInfo()" v-tippy="{arrow: true}" title="t">
                        <span>Close</span>
                        <span class="icon"><i class="fa fa-angle-double-right"></i></span>
                    </div>
                </div>
            </div>

            {{-- ====================================================================== --}}

            <div class="manager-container">
                {{-- files box --}}
                <div id="left">
                    <ul id="files" class="tile">
                        <li v-for="(file,index) in orderBy(filterBy(allFiles, searchFor, 'name'), showBy, -1)"
                            @click="setSelected(file)"
                            @dblclick="openFolder(file)">
                            <div class="file_link" :class="{'bulk-selected': IsInBulkList(file)}"
                                :data-item="file.name"
                                :data-index="index">
                                <div class="link_icon">
                                    <template v-if="fileTypeIs(file, 'image')">
                                        <div class="img" :style="{ 'background-image': 'url(' + file.path + ')' }"></div>
                                    </template>

                                    <span class="icon is-large" v-else>
                                        <i v-if="fileTypeIs(file, 'folder')" class="fa fa-folder fa-3x"></i>
                                        <i v-if="fileTypeIs(file, 'video')" class="fa fa-video-camera fa-3x"></i>
                                        <i v-if="fileTypeIs(file, 'audio')" class="fa fa-music fa-3x"></i>
                                        <i v-if="fileTypeIs(file, 'pdf')" class="fa fa-file-pdf-o fa-3x"></i>
                                        <i v-if="fileTypeIs(file, 'text')" class="fa fa-file-text fa-3x"></i>
                                    </span>
                                </div>
                                <div class="details">
                                    <h4 v-if="fileTypeIs(file, 'folder')">@{{ file.name }}</h4>
                                    <h4 v-else>@{{ getFileName(file.name) }}</h4>
                                    <small>
                                        <template v-if="fileTypeIs(file, 'folder')">
                                            <span>@{{ file.items }} item(s)</span>
                                            <span class="file_size" v-if="file.size > 0">, @{{ getFileSize(file.size) }}</span>
                                        </template>
                                        <template v-else>
                                            <span class="file_size">@{{ getFileSize(file.size) }}</span>
                                        </template>
                                    </small>
                                </div>
                            </div>
                        </li>
                    </ul>

                    {{-- ====================================================================== --}}

                    {{-- loading data from server --}}
                    <div id="file_loader" style="display: none;">
                        <div id="file_loader_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/octopus.json') }}"></div>
                        <h3>{{ trans('MediaManager::messages.loading') }}</h3>
                    </div>

                    {{-- no files --}}
                    <div id="no_files" style="display: none;">
                        <div id="no_files_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/zero.json') }}"></div>
                        <h3>{{ trans('MediaManager::messages.no_files_in_folder') }}</h3>
                    </div>

                    {{-- error --}}
                    <div id="ajax_error" style="display: none;">
                        <div id="ajax_error_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/avalanche.json') }}"></div>
                        <h3>{{ trans('MediaManager::messages.ajax_error') }}</h3>
                    </div>
                </div>

                {{-- ====================================================================== --}}

                {{-- info box --}}
                <div id="right" class="is-hidden-touch">
                    <div class="right_none_selected" v-if="!selectedFile">
                        <i class="fa fa-mouse-pointer"></i>
                        <p>{{ trans('MediaManager::messages.nothing_selected') }}</p>
                    </div>

                    <div class="right_details">
                        {{-- img / icon --}}
                        <template v-if="selectedFile">
                            <div class="detail_img">
                                <template v-if="selectedFileIs('image')">
                                    <div class="modal animated fadeIn mm-modal" id="img_modal">
                                        <div class="modal-background pointer" @click="toggleModal()"></div>
                                        <div class="modal-content animated fadeInDown">
                                            <p class="image">
                                                <img :src="selectedFile.path">
                                            </p>
                                        </div>
                                        <button class="modal-close is-large" @click="toggleModal()"></button>
                                    </div>
                                    <img :src="selectedFile.path"
                                        v-tippy="{position: 'right', arrow: true}"
                                        title="space" class="pointer"
                                        @click="toggleModal('#img_modal')"/>
                                </template>

                                <template v-if="selectedFileIs('video')">
                                    <video controls class="video player" :key="selectedFile.name"
                                        v-tippy="{position: 'right', arrow: true}" title="space">
                                        <source :src="selectedFile.path" type="video/mp4">
                                        <source :src="selectedFile.path" type="video/ogg">
                                        <source :src="selectedFile.path" type="video/webm">
                                        Your browser does not support the video tag.
                                    </video>
                                </template>

                                <template v-if="selectedFileIs('audio')">
                                    <audio controls class="audio player" :key="selectedFile.name"
                                        v-tippy="{position: 'right', arrow: true}" title="space">
                                        <source :src="selectedFile.path" type="audio/ogg">
                                        <source :src="selectedFile.path" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </template>

                                <i v-if="selectedFileIs('folder')" class="fa fa-folder"></i>
                                <i v-if="selectedFileIs('pdf')" class="fa fa-file-pdf-o"></i>
                                <i v-if="selectedFileIs('text')" class="fa fa-file-text-o"></i>
                            </div>

                            {{-- data --}}
                            <div class="detail_info">
                                <div>
                                    <h4>Title: <span>@{{ selectedFile.name }}</span></h4>
                                    <h4>Type: <span>@{{ selectedFile.type }}</span></h4>
                                    <h4>Size: <span>@{{ getFileSize(selectedFile.size) }}</span></h4>
                                    <template v-if="selectedFileIs('folder')">
                                        <h4>items: <span>@{{ selectedFile.items }} Item(s)</span></h4>
                                    </template>
                                    <template v-else>
                                        <h4>Public URL: <a :href="selectedFile.path" target="_blank">Click Here</a></h4>
                                    </template>
                                    <h4>Last Modified: <span>@{{ selectedFile.last_modified_formated }}</span></h4>
                                    <template v-if="!selectedFileIs('folder')">
                                        <h4>Download File:
                                            <a :href="selectedFile.path" @click.prevent="saveFile(selectedFile.path)">
                                                <span class="icon has-text-link"><i class="fa fa-download fa-lg"></i></span>
                                            </a>
                                        </h4>
                                    </template>
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

        {{-- ====================================================================== --}}

        {{-- modals --}}
        <div class="modal animated fadeIn mm-modal" id="new_folder_modal">
            {{ Form::open(['route' => 'media.new_folder', '@submit.prevent'=>'NewFolderForm($event)']) }}
                <div class="modal-background pointer" @click="toggleModal()"></div>
                <div class="modal-card animated fadeInDown">
                    <header class="modal-card-head is-link">
                        <p class="modal-card-title">
                            <span class="icon"><i class="fa fa-folder"></i></span>
                            <span>{{ trans('MediaManager::messages.add_new_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>
                    <section class="modal-card-body">
                        <input class="input" type="text"
                            placeholder="{{ trans('MediaManager::messages.new_folder_name') }}"
                            v-model="new_folder_name">
                    </section>
                    <footer class="modal-card-foot">
                        <button type="reset" class="button" @click="toggleModal()">
                            {{ trans('MediaManager::messages.cancel') }}
                        </button>
                        <button type="submit" class="button is-link" :disabled="is_loading" :class="{'is-loading': is_loading}">
                            {{ trans('MediaManager::messages.create_new_folder') }}
                        </button>
                    </footer>
                </div>
            {{ Form::close() }}
        </div>

        <div class="modal animated fadeIn mm-modal" id="rename_file_modal">
            {{ Form::open(['route' => 'media.rename_file', '@submit.prevent'=>'RenameFileForm($event)']) }}
                <div class="modal-background pointer" @click="toggleModal()"></div>
                <div class="modal-card animated fadeInDown">
                    <header class="modal-card-head is-warning">
                        <p class="modal-card-title">
                            <span class="icon"><i class="fa fa-i-cursor"></i></span>
                            <span>{{ trans('MediaManager::messages.rename_file_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>
                    <section class="modal-card-body">
                        <h3 class="title">{{ trans('MediaManager::messages.new_file_folder') }}</h3>
                        <input class="input" type="text"
                            v-if="selectedFile"
                            v-model="new_filename"
                            @focus="new_filename = selectedFileIs('folder') ? selectedFile.name : getFileName(selectedFile.name)">
                    </section>
                    <footer class="modal-card-foot">
                        <button type="reset" class="button" @click="toggleModal()">
                            {{ trans('MediaManager::messages.cancel') }}
                        </button>
                        <button type="submit" class="button is-warning" :disabled="is_loading" :class="{'is-loading': is_loading}">
                            {{ trans('MediaManager::messages.rename') }}
                        </button>
                    </footer>
                </div>
            {{ Form::close() }}
        </div>

        <div class="modal animated fadeIn mm-modal" id="move_file_modal">
            {{ Form::open(['route' => 'media.move_file', '@submit.prevent'=>'MoveFileForm($event)']) }}
                <div class="modal-background pointer" @click="toggleModal()"></div>
                <div class="modal-card animated fadeInDown">
                    <header class="modal-card-head is-warning">
                        <p class="modal-card-title">
                            <span class="icon"><i class="fa fa-share"></i></span>
                            <span>{{ trans('MediaManager::messages.move_file_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>
                    <section class="modal-card-body">
                        <h3 class="title">{{ trans('MediaManager::messages.destination_folder') }}</h3>
                        <div class="control has-icons-left">
                            <span class="select is-fullwidth">
                                <select id="move_folder_dropdown">
                                    <option v-if="moveUpCheck()" value="../">../</option>
                                    <option v-if="filterDirList(dir)"
                                        v-for="(dir,index) in directories"
                                        :key="index" :value="dir">
                                        @{{ dir }}
                                    </option>
                                </select>
                            </span>
                            <span class="icon is-small is-left">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                    </section>
                    <footer class="modal-card-foot">
                        <button type="reset" class="button" @click="toggleModal()">
                            {{ trans('MediaManager::messages.cancel') }}
                        </button>
                        <button type="submit" class="button is-warning" :disabled="is_loading" :class="{'is-loading': is_loading}">
                            {{ trans('MediaManager::messages.move') }}
                        </button>
                    </footer>
                </div>
            {{ Form::close() }}
        </div>

        <div class="modal animated fadeIn mm-modal" id="confirm_delete_modal">
            {{ Form::open(['route' => 'media.delete_file', '@submit.prevent'=>'DeleteFileForm($event)']) }}
                <div class="modal-background pointer" @click="toggleModal()"></div>
                <div class="modal-card animated fadeInDown">
                    <header class="modal-card-head is-danger">
                        <p class="modal-card-title">
                            <span class="icon"><i class="fa fa-warning"></i></span>
                            <span>{{ trans('MediaManager::messages.are_you_sure') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>
                    <section class="modal-card-body">
                        <h3 class="title">{{ trans('MediaManager::messages.are_you_sure_delete') }}</h3>
                        <template v-if="bulkItemsCount">
                            <table class="table" v-if="bulkItemsCount <= 8">
                                <tr class="confirm_delete_text" v-for="item in bulkList">
                                    <td width="1%">
                                        <span class="icon is-large">
                                            <i v-if="fileTypeIs(item, 'folder')" class="fa fa-folder fa-lg"></i>
                                            <i v-if="fileTypeIs(item, 'image')" class="fa fa-image fa-lg"></i>
                                            <i v-if="fileTypeIs(item, 'video')" class="fa fa-video-camera fa-lg"></i>
                                            <i v-if="fileTypeIs(item, 'audio')" class="fa fa-music fa-lg"></i>
                                            <i v-if="fileTypeIs(item, 'pdf')" class="fa fa-file-pdf fa-lg"></i>
                                            <i v-if="fileTypeIs(item, 'text')" class="fa fa-file-text fa-lg"></i>
                                        </span>
                                    </td>
                                    <td v-if="item.items" class="title is-5">@{{ item.name }} "@{{ item.items }} item(s)"</td>
                                    <td v-else class="title is-5">@{{ item.name }}</td>
                                </tr>
                            </table>
                            <template v-else>
                                <p class="m-l-50 confirm_delete_text">
                                    <span class="icon is-large"><i class="fa fa-archive fa-lg"></i></span>
                                    <span class="title is-5">{{ trans('MediaManager::messages.too_many_files') }}</span>
                                </p>
                            </template>
                        </template>

                        <template v-else>
                            <table class="table" v-if="selectedFile">
                                <tr class="confirm_delete_text">
                                    <td width="1%">
                                        <span class="icon is-large">
                                            <i v-if="selectedFileIs('folder')" class="fa fa-folder fa-lg"></i>
                                            <i v-if="selectedFileIs('image')" class="fa fa-image fa-lg"></i>
                                            <i v-if="selectedFileIs('video')" class="fa fa-video-camera fa-lg"></i>
                                            <i v-if="selectedFileIs('audio')" class="fa fa-music fa-lg"></i>
                                            <i v-if="selectedFileIs('pdf')" class="fa fa-file-pdf-o fa-lg"></i>
                                            <i v-if="selectedFileIs('text')" class="fa fa-file-text fa-lg"></i>
                                        </span>
                                    </td>
                                    <td id="confirm_delete" class="title is-5"></td>
                                </tr>
                            </table>
                        </template>
                        <h5 class="folder_warning">
                            <span class="icon"><i class="fa fa-warning"></i></span>
                            <span>{{ trans('MediaManager::messages.delete_folder_question') }}</span>
                        </h5>
                    </section>
                    <footer class="modal-card-foot">
                        <button type="reset" class="button" @click="toggleModal()">
                            {{ trans('MediaManager::messages.cancel') }}
                        </button>
                        <button type="submit" class="button is-danger" :disabled="is_loading" :class="{'is-loading': is_loading}">
                            {{ trans('MediaManager::messages.delete_confirm') }}
                        </button>
                    </footer>
                </div>
            {{ Form::close() }}
        </div>

    </div>
</media-manager>

{{-- scripts --}}
<script src="//cdnjs.cloudflare.com/ajax/libs/bodymovin/4.10.2/bodymovin.min.js"></script>
<script src="{{ asset('assets/vendor/MediaManager/manager.js') }}"></script>
