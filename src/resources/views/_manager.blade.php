{{-- styles --}}
<link rel="stylesheet" href="{{ asset('assets/vendor/MediaManager/style.css') }}"/>

{{-- component --}}
<media-manager inline-template
    base-url="{{ $base_url }}"
    :in-modal="{{ isset($modal) ? 'true' : 'false' }}"
    :hide-files-ext="{{ config('mediaManager.hide_files_ext') ? 'true' : 'false' }}"
    :hide-ext="{{ isset($hideExt) ? json_encode($hideExt) : '[]' }}"
    :hide-path="{{ isset($hidePath) ? json_encode($hidePath) : '[]' }}"
    :media-trans="{{ json_encode([
        'no_val' => trans('MediaManager::messages.no_val'),
        'single_char_folder' => trans('MediaManager::messages.single_char_folder'),
        'downloaded' => trans('MediaManager::messages.downloaded'),
        'upload_success' => trans('MediaManager::messages.upload_success')
    ]) }}"
    :upload-panel-img-list="{{ $patterns }}"
    files-route="{{ route('media.files') }}"
    dirs-route="{{ route('media.directories') }}"
    lock-file-route="{{ route('media.lock_file') }}"
    :restrict-path="{{ isset($path) ? $path : 'null' }}">

    <div>

        {{-- top toolbar --}}
        <nav id="toolbar" class="level" v-show="toolBar">

            {{-- left toolbar --}}
            <div class="level-left">
                <div class="level-item" v-if="!isBulkSelecting()">
                    <div class="field has-addons">
                        {{-- upload --}}
                        <div class="control">
                            <button class="button"
                                @click="toggleUploadPanel()"
                                v-tippy title="u">
                                <span class="icon is-small"><i class="fa fa-cloud-upload"></i></span>
                                <span>{{ trans('MediaManager::messages.upload') }}</span>
                            </button>
                        </div>

                        {{-- new folder --}}
                        <div class="control">
                            <button class="button"
                                @click="toggleModal('new_folder_modal')">
                                <span class="icon is-small"><i class="fa fa-folder"></i></span>
                                <span>{{ trans('MediaManager::messages.add_folder') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- refresh --}}
                <div class="level-item" v-if="!isBulkSelecting()">
                    <div class="control">
                        <button class="button is-light"
                            v-tippy title="r"
                            @click="getFiles(folders)">
                            <span class="icon is-small">
                                <i class="fa fa-refresh" :class="{'fa-spin': isLoading}"></i>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="level-item">
                    <div class="field has-addons">
                        {{-- move --}}
                        <div class="control">
                            <button class="button is-link"
                                v-multi-ref="'move'"
                                :disabled="mv_dl() || !checkForFolders"
                                v-tippy title="m"
                                @click="moveItem()">
                                <span class="icon is-small"><i class="fa fa-share"></i></span>
                                <span>{{ trans('MediaManager::messages.move') }}</span>
                            </button>
                        </div>

                        {{-- rename --}}
                        <div class="control">
                            <button class="button is-link"
                                :disabled="!selectedFile || IsInLockedList(selectedFile)"
                                v-if="!isBulkSelecting()"
                                @click="renameItem()">
                                <span class="icon is-small"><i class="fa fa-i-cursor"></i></span>
                                <span>{{ trans('MediaManager::messages.rename') }}</span>
                            </button>
                        </div>

                        {{-- delete --}}
                        <div class="control">
                            <button class="button is-link"
                                v-multi-ref="'delete'"
                                :disabled="mv_dl()"
                                v-tippy title="d / del"
                                @click="deleteItem()">
                                <span class="icon is-small"><i class="fa fa-trash"></i></span>
                                <span>{{ trans('MediaManager::messages.delete') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- lock --}}
                <div class="level-item">
                    <div class="control">
                        <button class="button is-warning"
                            ref="lock"
                            :disabled="searchItemsCount == 0 || !allItemsCount || isBulkSelecting() && !bulkItemsCount"
                            v-tippy title="l"
                            @click="pushToLockedList()">
                            <span class="icon is-small">
                                <i class="fa" :class="IsInLockedList(selectedFile) ? 'fa-unlock' : 'fa-lock'"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ====================================================================== --}}

            {{-- right toolbar --}}
            <div class="level-right">
                <div class="level-item">
                    <div class="field" :class="{'has-addons' : isBulkSelecting()}">
                        {{-- bulk select all --}}
                        <div class="control">
                            <button @click="blkSlctAll()"
                                ref="bulkSelectAll"
                                class="button"
                                :class="{'is-warning' : bulkSelectAll}"
                                v-show="isBulkSelecting()"
                                :disabled="searchItemsCount == 0"
                                v-tippy title="a">
                                <template v-if="bulkSelectAll">
                                    <span class="icon is-small"><i class="fa fa-minus"></i></span>
                                    <span>{{ trans('MediaManager::messages.select_non') }}</span>
                                </template>
                                <template v-else>
                                    <span class="icon is-small"><i class="fa fa-plus"></i></span>
                                    <span>{{ trans('MediaManager::messages.select_all') }}</span>
                                </template>
                            </button>
                        </div>

                        {{-- bulk select --}}
                        <div class="control">
                            <button @click="blkSlct()"
                                ref="bulkSelect"
                                class="button"
                                :class="{'is-danger' : bulkSelect}"
                                :disabled="!allItemsCount || searchItemsCount == 0"
                                v-tippy title="b">
                                <span class="icon is-small"><i class="fa fa-puzzle-piece"></i></span>
                                <span>{{ trans('MediaManager::messages.bulk_select') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <template v-if="allItemsCount">
                    {{-- filter by --}}
                    <div class="level-item" v-show="searchItemsCount != 0">
                        <div class="control">
                            <div class="field has-addons">
                                <div class="control">
                                    <button @click="showFilesOfType('image')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Image']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('image')}"
                                        :disabled="!btnFilter('image')">
                                        <span class="icon is-small"><i class="fa fa-image"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('video')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Video']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('video')}"
                                        :disabled="!btnFilter('video')">
                                        <span class="icon is-small"><i class="fa fa-video-camera"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('audio')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Audio']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('audio')}"
                                        :disabled="!btnFilter('audio')">
                                        <span class="icon is-small"><i class="fa fa-music"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('folder')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Folder']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('folder')}"
                                        :disabled="!btnFilter('folder')">
                                        <span class="icon is-small"><i class="fa fa-folder"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('text')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Text']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('text')}"
                                        :disabled="!btnFilter('text')">
                                        <span class="icon is-small"><i class="fa fa-file-text"></i></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('all')"
                                        v-tippy title="{{ trans('MediaManager::messages.clear',['attr'=>'filter']) }}"
                                        class="button"
                                        :class="{'is-danger': btnFilter('all')}"
                                        :disabled="!btnFilter('all')">
                                        <span class="icon is-small"><i class="fa fa-times"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- sort by --}}
                    <div class="level-item" v-show="searchItemsCount != 0">
                        <div class="control has-icons-left">
                            <div class="select">
                                <select v-model="sortBy">
                                    <option disabled value="null">{{ trans('MediaManager::messages.sort_by') }}</option>
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
                                    <input class="input"
                                        type="text"
                                        v-model="searchFor"
                                        data-search
                                        placeholder="{{ trans('MediaManager::messages.find') }}">
                                    <span class="icon is-small is-left">
                                        <i class="fa fa-search"></i>
                                    </span>
                                </p>
                                <p class="control">
                                    <button class="button is-black" :disabled="!searchFor"
                                        v-tippy title="{{ trans('MediaManager::messages.clear',['attr'=>'search']) }}"
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

        {{-- dropzone --}}
        <transition name="list">
            <div v-show="uploadToggle">
                <div class="dz">
                    <form id="new-upload" action="{{ route('media.upload') }}" :style="uploadPanelImg">
                        <div class="dz-message title is-4">{!! trans('MediaManager::messages.upload_text') !!}</div>
                        {{ csrf_field() }}
                        <input type="hidden" name="upload_path" :value="files.path ? files.path : '/'">

                        <div class="form-switcher" title="{{ trans('MediaManager::messages.use_random_names') }}" v-tippy>
                            <input type="checkbox" name="random_names" id="random_names" v-model="randomNames">
                            <label class="switcher" for="random_names"></label>
                        </div>
                    </form>

                    <div id="uploadPreview"></div>
                </div>

                <transition name="list">
                    <div id="uploadProgress" class="progress" v-show="uploadStart">
                        <div class="progress-bar is-success progress-bar-striped active" :style="{width: uploadProgress}"></div>
                    </div>
                </transition>
            </div>
        </transition>

        {{-- ====================================================================== --}}

        {{-- files area --}}
        <div class="level is-mobile breadcrumb-container">
            <div class="level-left">
                {{-- directories breadCrumb --}}
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
                            <a v-else v-tippy title="backspace">@{{ folder }}</a>
                        </li>
                    </template>
                </ol>
            </div>

            {{-- toggle info --}}
            <div class="level-right">
                <div class="toggle is-hidden-touch" @click="toggleInfoPanel()" v-tippy title="t">
                    <transition :name="toggleInfo ? 'info-out' : 'info-in'" mode="out-in">
                        <div key="1" v-if="toggleInfo">
                            <span>{{ trans('MediaManager::messages.close') }}</span>
                            <span class="icon"><i class="fa fa-angle-double-right"></i></span>
                        </div>
                        <div key="2" v-else>
                            <span>{{ trans('MediaManager::messages.open') }}</span>
                            <span class="icon"><i class="fa fa-angle-double-left"></i></span>
                        </div>
                    </transition>
                </div>

                <div class="is-hidden-desktop">
                    <button class="button is-link" @click="toolBar = !toolBar">
                        <span class="icon"><i class="fa fa-bars"></i></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ====================================================================== --}}

        <div class="manager-container">
            {{-- loadings --}}
            <section>
                {{-- loading data from server --}}
                <div id="file_loader" v-show="file_loader">
                    <div id="file_loader_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/octopus.json') }}"></div>
                    <h3>{{ trans('MediaManager::messages.loading') }}</h3>
                </div>

                {{-- no files --}}
                <div id="no_files" v-show="no_files">
                    <div id="no_files_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/zero.json') }}"></div>
                    <h3>{{ trans('MediaManager::messages.no_files_in_folder') }}</h3>
                </div>

                {{-- error --}}
                <div id="ajax_error" v-show="ajax_error">
                    <div id="ajax_error_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/avalanche.json') }}"></div>
                    <h3>{{ trans('MediaManager::messages.ajax_error') }}</h3>
                </div>
            </section>

            {{-- ====================================================================== --}}

            {{-- files box --}}
            <v-touch id="left"
                :class="{inModal: inModal}"
                @dbltap="selectedFileIs('image') ? toggleModal('preview_modal') : openFolder(selectedFile)"
                @swiperight="goToPrevFolder()"
                @swipeup="moveItem()"
                @swipedown="deleteItem()">

                <transition-group id="files" class="tile"
                    tag="ul" name="list" mode="out-in"
                    ref="filesList"
                    v-on:after-enter="afterEnter"
                    v-on:after-leave="afterLeave">
                    <li v-for="(file,index) in orderBy(filterBy(allFiles, searchFor, 'name'), sortBy, -1)"
                        :key="index"
                        @click="setSelected(file, index)">
                        <div class="file_link" :class="{'bulk-selected': IsInBulkList(file), 'selected' : selectedFile == file}"
                            :data-item="file.name"
                            :ref="'file_' + index">

                            {{-- lock file --}}
                            <div class="icon lock_icon"
                                :class="IsInLockedList(file) ? 'is-danger' : 'is-success'"
                                :title="IsInLockedList(file) ? '{{ trans('MediaManager::messages.unlock') }}': '{{ trans('MediaManager::messages.lock') }}'"
                                v-tippy="{arrow: true, hideOnClick: false}"
                                @click="toggleLock(file)">
                            </div>

                            {{-- copy file link --}}
                            <div v-if="!fileTypeIs(file, 'folder')"
                                class="icon copy_link"
                                @click="copyLink(file.path)"
                                :title="linkCopied ? '{{ trans('MediaManager::messages.copied') }}' : '{{ trans('MediaManager::messages.copy_to_cp') }}'"
                                v-tippy="{arrow: true, hideOnClick: false}"
                                @hidden="linkCopied = false">
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
                </transition-group>
            </v-touch>

            {{-- ====================================================================== --}}

            {{-- info box --}}
            <transition name="slide">
                <div id="right"
                    class="is-hidden-touch"
                    :class="{inModal: inModal}"
                    v-if="toggleInfo">
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
                                        title="space" class="link"
                                        @click="toggleModal('preview_modal')"/>
                                </template>

                                <template v-if="selectedFileIs('video')">
                                    <video controls class="video"
                                        ref="player"
                                        :key="selectedFile.name"
                                        v-tippy="{position: 'left', arrow: true}" title="space">
                                        <source :src="selectedFile.path" type="video/mp4">
                                        <source :src="selectedFile.path" type="video/ogg">
                                        <source :src="selectedFile.path" type="video/webm">
                                        {{ trans('MediaManager::messages.video_support') }}
                                    </video>
                                </template>

                                <template v-if="selectedFileIs('audio')">
                                    <audio controls class="audio"
                                        ref="player"
                                        :key="selectedFile.name"
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
                                            <button class="btn-plain"
                                                @click.prevent="saveFile(selectedFile)"
                                                title="{{ trans('MediaManager::messages.download_file') }}"
                                                v-tippy>
                                                <span class="icon has-text-black"><i class="fa fa-download fa-lg"></i></span>
                                            </button>
                                        </h4>
                                    </template>
                                    <h4>{{ trans('MediaManager::messages.last_modified') }}: <span>@{{ selectedFile.last_modified_formated }}</span></h4>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- items count --}}
                    <transition-group class="count" tag="ul" name="slide" mode="out-in">
                        <li key="1" class="title is-marginless" v-if="bulkItemsCount">
                            @{{ bulkItemsCount }} {{ trans('MediaManager::messages.selected') }}
                        </li>
                        <li key="2" class="title is-marginless" v-if="searchItemsCount !== null && searchItemsCount >= 0">
                            @{{ searchItemsCount }} {{ trans('MediaManager::messages.found') }}
                        </li>
                        <li key="3" class="title is-marginless" v-if="allItemsCount">
                            @{{ allItemsCount }} {{ trans('MediaManager::messages.total') }}
                        </li>
                    </transition-group>
                </div>
            </transition>
        </div>

        {{-- ====================================================================== --}}

        {{-- modals --}}
        <section>
            {{-- preview_modal --}}
            <div id="preview_modal"
                v-if="isActiveModal('preview_modal')"
                class="modal mm-animated fadeIn is-active">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="mm-animated fadeInDown">
                    <transition mode="out-in" :name="navDirection == 'next' ? 'img-nxt' : 'img-prv'">
                        <div class="modal-content" :key="selectedFile.path">
                            {{-- card v --}}
                            @include('MediaManager::cards.vertical')
                        </div>
                    </transition>
                </div>
                <button class="modal-close is-large" @click="toggleModal()"></button>
            </div>

            {{-- new_folder_modal --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('new_folder_modal')}"
                v-show="isActiveModal('new_folder_modal')">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-link">
                        <p class="modal-card-title">
                            <span class="icon"><i class="fa fa-folder"></i></span>
                            <span>{{ trans('MediaManager::messages.add_new_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.new_folder') }}" @submit.prevent="NewFolderForm($event)">
                        <section class="modal-card-body">
                            <input class="input" type="text"
                                v-model="new_folder_name"
                                placeholder="{{ trans('MediaManager::messages.new_folder_name') }}"
                                ref="new_folder_modal_input">
                        </section>
                        <footer class="modal-card-foot">
                            <button type="reset" class="button" @click="toggleModal()">
                                {{ trans('MediaManager::messages.cancel') }}
                            </button>
                            <button type="submit"
                                class="button is-link"
                                :disabled="isLoading"
                                :class="{'is-loading': isLoading}">
                                {{ trans('MediaManager::messages.create_new_folder') }}
                            </button>
                        </footer>
                    </form>
                </div>
            </div>

            {{-- rename_file_modal --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('rename_file_modal')}"
                v-show="isActiveModal('rename_file_modal')">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-warning">
                        <p class="modal-card-title">
                            <span class="icon"><i class="fa fa-i-cursor"></i></span>
                            <span>{{ trans('MediaManager::messages.rename_file_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.rename_file') }}" @submit.prevent="RenameFileForm($event)">
                        <section class="modal-card-body">
                            <h3 class="title">{{ trans('MediaManager::messages.new_file_folder') }}</h3>
                            <input class="input" type="text"
                                v-if="selectedFile"
                                v-model="new_filename"
                                ref="rename_file_modal_input"
                                @focus="new_filename = selectedFileIs('folder') ? selectedFile.name : getFileName(selectedFile.name)">
                        </section>
                        <footer class="modal-card-foot">
                            <button type="reset" class="button" @click="toggleModal()">
                                {{ trans('MediaManager::messages.cancel') }}
                            </button>
                            <button type="submit"
                                class="button is-warning"
                                :disabled="isLoading"
                                :class="{'is-loading': isLoading}">
                                {{ trans('MediaManager::messages.rename') }}
                            </button>
                        </footer>
                    </form>
                </div>
            </div>

            {{-- move_file_modal --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('move_file_modal')}"
                v-show="isActiveModal('move_file_modal')">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-warning">
                        <p class="modal-card-title">
                            <span class="icon"><i class="fa fa-share"></i></span>
                            <span>{{ trans('MediaManager::messages.move_file_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.move_file') }}" @submit.prevent="MoveFileForm($event)">
                        <section class="modal-card-body">
                            <h3 class="title">{{ trans('MediaManager::messages.destination_folder') }}</h3>
                            <div class="control has-icons-left">
                                <span class="select is-fullwidth">
                                    <select ref="move_folder_dropdown" v-model="moveToPath">
                                        <option v-if="moveUpCheck()" value="../">../</option>
                                        <option v-for="(dir,index) in directories"
                                            v-if="filterDirList(dir)"
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
                            <button type="submit"
                                class="button is-warning"
                                :disabled="isLoading || !moveToPath"
                                :class="{'is-loading': isLoading}">
                                {{ trans('MediaManager::messages.move') }}
                            </button>
                        </footer>
                    </form>
                </div>
            </div>

            {{-- confirm_delete_modal --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('confirm_delete_modal')}"
                v-show="isActiveModal('confirm_delete_modal')">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-danger">
                        <p class="modal-card-title">
                            <span class="icon"><i class="fa fa-warning"></i></span>
                            <span>{{ trans('MediaManager::messages.are_you_sure_delete') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.delete_file') }}" @submit.prevent="DeleteFileForm($event)">
                        <section class="modal-card-body">
                            <template v-if="bulkListFilter.length">
                                <table class="table" v-if="bulkListFilter.length <= 8">
                                    <tr class="confirm_delete_text" v-for="item in bulkListFilter">
                                        <td width="1%">
                                            <span class="icon is-large">
                                                <i v-if="fileTypeIs(item, 'folder')" class="fa fa-folder fa-lg"></i>
                                                <i v-if="fileTypeIs(item, 'image')" class="fa fa-image fa-lg"></i>
                                                <i v-if="fileTypeIs(item, 'video')" class="fa fa-video-camera fa-lg"></i>
                                                <i v-if="fileTypeIs(item, 'audio')" class="fa fa-music fa-lg"></i>
                                                <i v-if="fileTypeIs(item, 'pdf')" class="fa fa-file-pdf-o fa-lg"></i>
                                                <i v-if="fileTypeIs(item, 'text')" class="fa fa-file-text fa-lg"></i>
                                            </span>
                                        </td>
                                        <td v-if="item.items" class="title is-5">@{{ item.name }} "@{{ item.items }} {{ trans('MediaManager::messages.items') }}"</td>
                                        <td v-else class="title is-5">@{{ item.name }}</td>
                                    </tr>
                                </table>

                                <p class="m-l-50 confirm_delete_text" v-else>
                                    <span class="icon is-large"><i class="fa fa-archive fa-lg"></i></span>
                                    <span class="title is-5">{{ trans('MediaManager::messages.too_many_files') }}</span>
                                </p>
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
                                        <td id="confirm_delete" class="title is-5" ref="confirm_delete"></td>
                                    </tr>
                                </table>
                            </template>

                            <h5 class="folder_warning" v-if="folderWarning">
                                <span class="icon"><i class="fa fa-warning"></i></span>
                                <span>{{ trans('MediaManager::messages.delete_folder') }}</span>
                            </h5>
                        </section>
                        <footer class="modal-card-foot">
                            <button type="reset" class="button" @click="toggleModal()">
                                {{ trans('MediaManager::messages.cancel') }}
                            </button>
                            <button type="submit"
                                ref="confirm_delete_modal_submit"
                                class="button is-danger"
                                :disabled="isLoading"
                                :class="{'is-loading': isLoading}">
                                {{ trans('MediaManager::messages.delete_confirm') }}
                            </button>
                        </footer>
                    </form>
                </div>
            </div>
        </section>

    </div>
</media-manager>

{{-- scripts --}}
<script src="//cdnjs.cloudflare.com/ajax/libs/bodymovin/4.10.2/bodymovin.min.js"></script>
<script src="{{ asset('assets/vendor/MediaManager/manager.js') }}"></script>
