{{-- styles --}}
<link rel="stylesheet" href="{{ asset('assets/vendor/MediaManager/style.css') }}"/>

{{-- component --}}
@php
    $trans = [
        'all' => trans('MediaManager::messages.select_all'),
        'non' => trans('MediaManager::messages.select_non'),
        'close' => trans('MediaManager::messages.close'),
        'open' => trans('MediaManager::messages.open')
    ];
@endphp

<media-manager inline-template
    files-route="{{ route('media.files') }}"
    dirs-route="{{ route('media.directories') }}"
    :hide-ext="{{ config('mediaManager.hide_ext') ? 'true' : 'false' }}"
    restrict-path="{{ isset($path) ? $path : null }}"
    restrict-ext="{{ isset($ext) ? json_encode($ext) : null }}"
    :trans="{{ json_encode($trans) }}">
    <v-touch @swiperight="toggleModal()">

        {{-- top toolbar --}}
        <nav id="toolbar" class="level">

            {{-- left toolbar --}}
            <div class="level-left">
                {{-- manager --}}
                <div class="level-item">
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

                <div class="level-item">
                    <div class="control">
                        <button class="button is-light" id="refresh"
                            v-tippy="{arrow: true}" title="r"
                            @click="getFiles(folders)">
                            <span class="icon is-small"><i class="fa fa-refresh"></i></span>
                        </button>
                    </div>
                </div>

                <div class="level-item">
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
                            <button class="button is-link" id="rename"
                                @click="toggleModal('#rename_file_modal')">
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

            {{-- ====================================================================== --}}

            {{-- right toolbar --}}
            <div class="level-right">
                <div class="level-item">
                    {{-- multi --}}
                    <div class="field">
                        <div class="control">
                            <button id="blk_slct_all"  class="button"
                                v-tippy="{arrow: true}" title="a">
                                <span class="icon is-small"><i class="fa fa-plus"></i></span>
                                <span>{{ trans('MediaManager::messages.select_all') }}</span>
                            </button>
                        </div>
                        @if (!isset($no_bulk))
                            <div class="control">
                                <button id="blk_slct"  class="button"
                                    :disabled="!allItemsCount"
                                    v-tippy="{arrow: true}" title="b">
                                    <span class="icon is-small"><i class="fa fa-puzzle-piece"></i></span>
                                    <span>{{ trans('MediaManager::messages.bulk_select') }}</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <template v-if="allItemsCount">
                    {{-- filter by --}}
                    <div class="level-item">
                        <div class="control">
                            <div class="field has-addons">
                                <div class="control">
                                    <button @click="showFilesOfType('image')"
                                        v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Image']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('image')}"
                                        :disabled="!btnFilter('image')">
                                        <span class="icon is-small"><i class="fa fa-image"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('video')"
                                        v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Video']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('video')}"
                                        :disabled="!btnFilter('video')">
                                        <span class="icon is-small"><i class="fa fa-video-camera"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('audio')"
                                        v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Audio']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('audio')}"
                                        :disabled="!btnFilter('audio')">
                                        <span class="icon is-small"><i class="fa fa-music"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('folder')"
                                        v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Folder']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('folder')}"
                                        :disabled="!btnFilter('folder')">
                                        <span class="icon is-small"><i class="fa fa-folder"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('text')"
                                        v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Text']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('text')}"
                                        :disabled="!btnFilter('text')">
                                        <span class="icon is-small"><i class="fa fa-file-text"></i></span>
                                    </button>
                                </div>

                                <div class="control">
                                    <button @click="showFilesOfType('all')"
                                        v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.clear',['attr'=>'filter']) }}"
                                        class="button"
                                        :class="{'is-danger': btnFilter('all')}"
                                        :disabled="!btnFilter('all')">
                                        <span class="icon is-small"><i class="fa fa-times"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- showBy --}}
                    <div class="level-item">
                        <div class="control has-icons-left">
                            <div class="select">
                                <select v-model="showBy">
                                    <option disabled value="undefined">{{ trans('MediaManager::messages.sort_by') }}</option>
                                    <option value="clear">{{ trans('MediaManager::messages.non') }}</option>
                                    <option value="size">{{ trans('MediaManager::messages.size') }}</option>
                                    <option value="last_modified">{{ trans('MediaManager::messages.last_modified') }}</option>
                                </select>
                            </div>
                            <div class="icon is-small is-left">
                                <i class="fa fa-bell-o"></i>
                            </div>
                        </div>
                    </div>

                    {{-- search --}}
                    <div class="level-item">
                        <div class="control">
                            <div class="field has-addons">
                                <p class="control has-icons-left">
                                    <input class="input" type="text" placeholder="{{ trans('MediaManager::messages.find') }}" v-model="searchFor">
                                    <span class="icon is-small is-left">
                                        <i class="fa fa-search"></i>
                                    </span>
                                </p>
                                <p class="control">
                                    <button class="button is-black" :disabled="!searchFor"
                                        v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.clear',['attr'=>'search']) }}"
                                        @click="resetInput('searchFor')" >
                                        <span class="icon is-small"><i class="fa fa-times"></i></span>
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </nav>

        {{-- ====================================================================== --}}

        {{-- upload --}}
        <div class="field is-marginless">
            <div id="dz">
                <form class="dz" id="new-upload" action="{{ route('media.upload') }}">
                    <div class="dz-message title is-4">{!! trans('MediaManager::messages.upload_text') !!}</div>
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
                        <li v-if="!checkForRestrictedPath()">
                            <a v-if="folders.length > 0 && !isBulkSelecting()" class="p-l-0" @click="goToFolder(0)">
                                {{ trans('MediaManager::messages.library') }}
                            </a>
                            <p v-else class="p-l-0">{{ trans('MediaManager::messages.library') }}</p>
                        </li>

                        <template v-for="(folder,index) in folders">
                            <li @click="folders.length > 1 ? goToFolder(index+1) : false">
                                <p v-if="isLastItem(folder, folders)">@{{ folder }}</p>
                                <a v-else v-tippy="{arrow: true}" title="backspace">@{{ folder }}</a>
                            </li>
                        </template>
                    </ol>
                </div>

                <div class="level-right is-hidden-touch">
                    <div class="toggle" @click="toggleInfo()" v-tippy="{arrow: true}" title="t">
                        <span>{{ trans('MediaManager::messages.close') }}</span>
                        <span class="icon"><i class="fa fa-angle-double-right"></i></span>
                    </div>
                </div>
            </div>

            {{-- ====================================================================== --}}

            <div class="manager-container">
                {{-- files box --}}
                <v-touch id="left"
                    @swiperight="goToPrevFolder()"
                    @dbltap="selectedFileIs('image') ? toggleModal('#preview_modal') : openFolder(selectedFile)">

                    <ul id="files" class="tile">
                        <li v-for="(file,index) in orderBy(filterBy(allFiles, searchFor, 'name'), showBy, -1)"
                            :key="index"
                            @click="setSelected(file)">
                            <div class="file_link" :class="{'bulk-selected': IsInBulkList(file)}"
                                :data-item="file.name"
                                :data-index="index">

                                <div v-if="!fileTypeIs(file, 'folder')"
                                    class="icon copy-link"
                                    @click="copyLink(file.path)"
                                    :title="linkCopied ? '{{ trans('MediaManager::messages.copied') }}' : '{{ trans('MediaManager::messages.copy_to_cp') }}'"
                                    v-tippy="{arrow: true, hideOnClick: false}"
                                    @Hidden="linkCopied = false">
                                    <i class="fa fa-clone" aria-hidden="true"></i>
                                </div>

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
                                    <template v-if="fileTypeIs(file, 'folder')">
                                        <h4>@{{ file.name }}</h4>
                                        <small>
                                            <span>@{{ file.items }} {{ trans('MediaManager::messages.items') }}</span>
                                            <span v-if="file.size > 0" class="file_size">, @{{ getFileSize(file.size) }}</span>
                                        </small>
                                    </template>

                                    <template v-else>
                                        <h4>@{{ getFileName(file.name) }}</h4>
                                        <small>
                                            <span class="file_size">@{{ getFileSize(file.size) }}</span>
                                        </small>
                                    </template>
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
                </v-touch>

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
                                    <img :src="selectedFile.path"
                                        v-tippy="{position: 'left', arrow: true}"
                                        title="space" class="pointer"
                                        @click="toggleModal('#preview_modal')"/>
                                </template>

                                <template v-if="selectedFileIs('video')">
                                    <video controls class="video player" :key="selectedFile.name"
                                        v-tippy="{position: 'left', arrow: true}" title="space">
                                        <source :src="selectedFile.path" type="video/mp4">
                                        <source :src="selectedFile.path" type="video/ogg">
                                        <source :src="selectedFile.path" type="video/webm">
                                        {{ trans('MediaManager::messages.video_support') }}
                                    </video>
                                </template>

                                <template v-if="selectedFileIs('audio')">
                                    <audio controls class="audio player" :key="selectedFile.name"
                                        v-tippy="{position: 'left', arrow: true}" title="space">
                                        <source :src="selectedFile.path" type="audio/ogg">
                                        <source :src="selectedFile.path" type="audio/mpeg">
                                        {{ trans('MediaManager::messages.audio_support') }}
                                    </audio>
                                </template>

                                <i v-if="selectedFileIs('folder')" class="fa fa-folder"></i>
                                <i v-if="selectedFileIs('pdf')" class="fa fa-file-pdf-o"></i>
                                <i v-if="selectedFileIs('text')" class="fa fa-file-text-o"></i>
                            </div>

                            {{-- data --}}
                            <div class="detail_info">
                                <div>
                                    <h4>{{ trans('MediaManager::messages.title') }}: <span>@{{ selectedFile.name }}</span></h4>
                                    <h4>{{ trans('MediaManager::messages.type') }}: <span>@{{ selectedFile.type }}</span></h4>
                                    <h4>{{ trans('MediaManager::messages.size') }}: <span>@{{ getFileSize(selectedFile.size) }}</span></h4>
                                    <template v-if="selectedFileIs('folder')">
                                        <h4>{{ trans('MediaManager::messages.items') }}: <span>@{{ selectedFile.items }} {{ trans('MediaManager::messages.items') }}</span></h4>
                                    </template>
                                    <template v-else>
                                        <h4>
                                            <a :href="selectedFile.path" class="has-text-link" target="_blank">{{ trans('MediaManager::messages.public_url') }}</a>
                                            <a :href="selectedFile.path"
                                                @click.prevent="saveFile(selectedFile.path)"
                                                v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.download_file') }}">
                                                <span class="icon has-text-black"><i class="fa fa-download fa-lg"></i></span>
                                            </a>
                                        </h4>
                                    </template>
                                    <h4>{{ trans('MediaManager::messages.last_modified') }}: <span>@{{ selectedFile.last_modified_formated }}</span></h4>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- items count --}}
                    <div class="count" v-if="allItemsCount">
                        <p class="title is-marginless" v-if="bulkItemsCount">
                            @{{ bulkItemsCount }} {{ trans('MediaManager::messages.selected') }}
                        </p>
                        <p class="title is-marginless" v-if="searchItemsCount !== null && searchItemsCount >= 0">
                            @{{ searchItemsCount }} {{ trans('MediaManager::messages.found') }}
                        </p>
                        <p class="title is-marginless">
                            @{{ allItemsCount }} {{ trans('MediaManager::messages.total') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================================================================== --}}

        {{-- modals --}}
        <div class="modal mm-animated fadeIn mm-modal" id="preview_modal" v-if="selectedFileIs('image')">
            <div class="modal-background pointer" @click="toggleModal()"></div>
            <div class="modal-content mm-animated fadeInDown">
                <p class="image">
                    <img :src="selectedFile.path">
                </p>
            </div>
            <button class="modal-close is-large" @click="toggleModal()"></button>
        </div>

        <div class="modal mm-animated fadeIn mm-modal" id="new_folder_modal">
            {{ Form::open(['route' => 'media.new_folder', '@submit.prevent'=>'NewFolderForm($event)']) }}
                <div class="modal-background pointer" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
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

        <div class="modal mm-animated fadeIn mm-modal" id="rename_file_modal">
            {{ Form::open(['route' => 'media.rename_file', '@submit.prevent'=>'RenameFileForm($event)']) }}
                <div class="modal-background pointer" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
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

        <div class="modal mm-animated fadeIn mm-modal" id="move_file_modal">
            {{ Form::open(['route' => 'media.move_file', '@submit.prevent'=>'MoveFileForm($event)']) }}
                <div class="modal-background pointer" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
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

        <div class="modal mm-animated fadeIn mm-modal" id="confirm_delete_modal">
            {{ Form::open(['route' => 'media.delete_file', '@submit.prevent'=>'DeleteFileForm($event)']) }}
                <div class="modal-background pointer" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
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
                                    <td v-if="item.items" class="title is-5">@{{ item.name }} "@{{ item.items }} {{ trans('MediaManager::messages.items') }}"</td>
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
                            <span>{{ trans('MediaManager::messages.delete_folder') }}</span>
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

    </v-touch>
</media-manager>

{{-- scripts --}}
<script src="//cdnjs.cloudflare.com/ajax/libs/bodymovin/4.10.2/bodymovin.min.js"></script>
<script src="{{ asset('assets/vendor/MediaManager/manager.js') }}"></script>
