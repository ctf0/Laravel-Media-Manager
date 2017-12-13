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

    <div :class="{inModal: inModal}" class="">

        {{-- top toolbar --}}
        <nav class="media-manager__toolbar level" v-show="toolBar">

            {{-- left toolbar --}}
            <div class="level-left">
                <div class="level-item" v-if="!isBulkSelecting()">
                    <div class="field has-addons">
                        {{-- upload --}}
                        <div class="control">
                            <button class="button"
                                @click="toggleUploadPanel()"
                                v-tippy title="u">
                                <span class="icon is-small"><icon name="shopping-basket"></icon></span>
                                <span>{{ trans('MediaManager::messages.upload') }}</span>
                            </button>
                        </div>

                        {{-- new folder --}}
                        <div class="control">
                            <button class="button"
                                @click="toggleModal('new_folder_modal')">
                                <span class="icon is-small"><icon name="folder"></icon></span>
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
                            @click="refresh()">
                            <span class="icon is-small">
                                <icon name="refresh" :spin="isLoading"></icon>
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
                                <span class="icon is-small"><icon name="share"></icon></span>
                                <span>{{ trans('MediaManager::messages.move') }}</span>
                            </button>
                        </div>

                        {{-- rename --}}
                        <div class="control">
                            <button class="button is-link"
                                :disabled="!selectedFile || IsInLockedList(selectedFile)"
                                v-if="!isBulkSelecting()"
                                @click="renameItem()">
                                <span class="icon is-small"><icon name="i-cursor"></icon></span>
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
                                <span class="icon is-small"><icon name="trash"></icon></span>
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
                                <icon :name="IsInLockedList(selectedFile) ? 'unlock' : 'lock'"></icon>
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
                                    <span class="icon is-small"><icon name="minus" scale="0.8"></icon></span>
                                    <span>{{ trans('MediaManager::messages.select_non') }}</span>
                                </template>
                                <template v-else>
                                    <span class="icon is-small"><icon name="plus" scale="0.8"></icon></span>
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
                                <span class="icon is-small"><icon name="puzzle-piece"></icon></span>
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
                                        <span class="icon is-small"><icon name="image"></icon></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('video')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Video']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('video')}"
                                        :disabled="!btnFilter('video')">
                                        <span class="icon is-small"><icon name="video-camera"></icon></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('audio')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Audio']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('audio')}"
                                        :disabled="!btnFilter('audio')">
                                        <span class="icon is-small"><icon name="music"></icon></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('folder')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Folder']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('folder')}"
                                        :disabled="!btnFilter('folder')">
                                        <span class="icon is-small"><icon name="folder"></icon></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('text')"
                                        v-tippy title="{{ trans('MediaManager::messages.filter_by', ['attr'=>'Text']) }}"
                                        class="button"
                                        :class="{'is-link': filterNameIs('text')}"
                                        :disabled="!btnFilter('text')">
                                        <span class="icon is-small"><icon name="file-text-o"></icon></span>
                                    </button>
                                </div>
                                <div class="control">
                                    <button @click="showFilesOfType('all')"
                                        v-tippy title="{{ trans('MediaManager::messages.clear',['attr'=>'filter']) }}"
                                        class="button"
                                        :class="{'is-danger': btnFilter('all')}"
                                        :disabled="!btnFilter('all')">
                                        <span class="icon is-small"><icon name="times"></icon></span>
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
                                <icon name="bell-o"></icon>
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
                                        <icon name="search"></icon>
                                    </span>
                                </p>
                                <p class="control">
                                    <button class="button is-black" :disabled="!searchFor"
                                        v-tippy title="{{ trans('MediaManager::messages.clear',['attr'=>'search']) }}"
                                        @click="resetInput('searchFor')" >
                                        <span class="icon is-small"><icon name="times"></icon></span>
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
        <transition name="list" mode="out-in">
            <div v-show="uploadToggle">
                <div class="media-manager__dz">
                    <form id="new-upload" action="{{ route('media.upload') }}" :style="uploadPanelImg">
                        <div class="__dz-message dz-message title is-4">{!! trans('MediaManager::messages.upload_text') !!}</div>
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

        <div class="media-manager__stack">
            <section class="media-manager__stack-files">
                {{-- loadings --}}
                <section>
                    {{-- loading data from server --}}
                    <div id="loading_files" v-show="loading_files">
                        <div id="loading_files_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/world.json') }}"></div>
                        <h3>{{ trans('MediaManager::messages.loading') }}</h3>
                    </div>

                    {{-- ajax error --}}
                    <div id="ajax_error" v-show="ajax_error">
                        <div id="ajax_error_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/avalanche.json') }}"></div>
                        <h3>{{ trans('MediaManager::messages.ajax_error') }}</h3>
                    </div>

                    {{-- no files --}}
                    <div id="no_files" v-show="no_files">
                        <div id="no_files_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/zero.json') }}"></div>
                        <h3>{{ trans('MediaManager::messages.no_files_in_folder') }}</h3>
                    </div>
                </section>

                {{-- ====================================================================== --}}

                {{-- files box --}}
                <v-touch class="media-manager__stack-left"
                    :class="{'__stack-right-hidden': !toggleInfo}"
                    ref="__stackLeft"
                    @dbltap="dbltap()"
                    @swiperight="goToPrevFolder()"
                    @swipeup="moveItem()"
                    @swipedown="deleteItem()">

                    {{-- loadings --}}
                    <section>
                        {{-- no search --}}
                        <div id="no_search" v-show="no_search">
                            <div id="no_search_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/ice_cream.json') }}"></div>
                            <h3>{{ trans('MediaManager::messages.nothing_found') }}</h3>
                        </div>
                    </section>

                    {{-- files --}}
                    <transition-group class="__left-files"
                        tag="ul" name="list" mode="out-in"
                        ref="filesList"
                        v-on:after-enter="afterEnter"
                        v-on:after-leave="afterLeave">
                        <li v-for="(file,index) in orderBy(filterBy(allFiles, searchFor, 'name'), sortBy, -1)"
                            :key="index"
                            @click="setSelected(file, index)">
                            <div class="__file-box" :class="{'bulk-selected': IsInBulkList(file), 'selected' : selectedFile == file}"
                                :data-item="file.name"
                                :ref="'file_' + index">

                                {{-- lock file --}}
                                <div class="__box-lock-icon icon"
                                    :class="IsInLockedList(file) ? 'is-danger' : 'is-success'"
                                    :title="IsInLockedList(file) ? '{{ trans('MediaManager::messages.unlock') }}': '{{ trans('MediaManager::messages.lock') }}'"
                                    v-tippy="{arrow: true, hideOnClick: false}"
                                    @click="toggleLock(file)">
                                </div>

                                {{-- copy file link --}}
                                <div v-if="!fileTypeIs(file, 'folder')"
                                    class="__box-copy-link icon"
                                    @click="copyLink(file.path)"
                                    :title="linkCopied ? '{{ trans('MediaManager::messages.copied') }}' : '{{ trans('MediaManager::messages.copy_to_cp') }}'"
                                    v-tippy="{arrow: true, hideOnClick: false}"
                                    @hidden="linkCopied = false">
                                    <icon name="clone" scale="0.9"></icon>
                                </div>

                                <div class="__box-data">
                                    <div class="__box-preview">
                                        <template v-if="fileTypeIs(file, 'image')">
                                            <div class="__box-img" :style="{ 'background-image': 'url(' + file.path + ')' }"></div>
                                        </template>

                                        <span class="icon is-large" v-else>
                                            <icon v-if="fileTypeIs(file, 'folder')" name="folder" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'video')" name="film" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'audio')" name="music" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'pdf')" name="file-pdf-o" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'text')" name="file-text-o" scale="2.6"></icon>
                                        </span>
                                    </div>

                                    <div class="__box-info">
                                        <template v-if="fileTypeIs(file, 'folder')">
                                            <h4>@{{ file.name }}</h4>
                                            <small>
                                                <span>@{{ file.items }} {{ trans('MediaManager::messages.items') }}</span>
                                                <span v-if="file.size > 0" class="__info-file-size">, @{{ getFileSize(file.size) }}</span>
                                            </small>
                                        </template>

                                        <template v-else>
                                            <h4>@{{ getFileName(file.name) }}</h4>
                                            <small>
                                                <span class="__info-file-size">@{{ getFileSize(file.size) }}</span>
                                            </small>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </transition-group>
                </v-touch>

                {{-- ====================================================================== --}}

                {{-- info sidebar --}}
                <transition name="slide" mode="out-in" appear>
                    <div class="media-manager__stack-right is-hidden-touch" v-if="toggleInfo">
                        {{-- preview --}}
                        <div class="__right-preview">
                            <transition name="slide" mode="out-in" appear>
                                {{-- no selection --}}
                                <div key="0" class="__right-none-selected" v-if="!selectedFile">
                                    <icon name="mouse-pointer" scale="2"></icon>
                                    <p>{{ trans('MediaManager::messages.nothing_selected') }}</p>
                                </div>

                                {{-- img --}}
                                <template v-if="selectedFileIs('image')">
                                    <img :src="selectedFile.path"
                                        :key="selectedFile.name"
                                        v-tippy="{position: 'left'}"
                                        title="space"
                                        class="link image"
                                        @click="toggleModal('preview_modal')"/>
                                </template>

                                {{-- video --}}
                                <template v-if="selectedFileIs('video')">
                                    <video controls preload="metadata" class="__right-video"
                                        ref="player"
                                        :key="selectedFile.name"
                                        v-tippy="{position: 'left'}" title="space">
                                        <source :src="selectedFile.path" type="video/mp4">
                                        <source :src="selectedFile.path" type="video/ogg">
                                        <source :src="selectedFile.path" type="video/webm">
                                        {{ trans('MediaManager::messages.video_support') }}
                                    </video>
                                </template>

                                {{-- audio --}}
                                <template v-if="selectedFileIs('audio')">
                                    <audio controls preload="metadata" class="__right-audio"
                                        ref="player"
                                        :key="selectedFile.name"
                                        v-tippy="{position: 'left'}" title="space">
                                        <source :src="selectedFile.path" type="audio/ogg">
                                        <source :src="selectedFile.path" type="audio/mpeg">
                                        {{ trans('MediaManager::messages.audio_support') }}
                                    </audio>
                                </template>

                                {{-- icons --}}
                                <icon v-if="selectedFileIs('folder')" name="folder" scale="4"></icon>
                                <div key="1" v-if="selectedFileIs('pdf')" class="link"
                                    v-tippy="{position: 'left'}" title="space"
                                    @click="toggleModal('preview_modal')">
                                    <icon name="file-pdf-o" scale="4"></icon>
                                </div>
                                <div key="2" v-if="selectedFileIs('text')" class="link"
                                    v-tippy="{position: 'left'}" title="space"
                                    @click="toggleModal('preview_modal')">
                                    <icon name="file-text-o" scale="4"></icon>
                                </div>
                            </transition>
                        </div>

                        {{-- data --}}
                        <div class="__right-info" v-if="allItemsCount" :style="selectedFile ? 'background-color: white' : ''">
                            <transition name="list" mode="out-in" appear>
                                <div :key="selectedFile.name" v-if="selectedFile">
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
                                                <span class="icon has-text-black"><icon name="download"></icon></span>
                                            </button>
                                        </h4>
                                    </template>
                                    <h4>{{ trans('MediaManager::messages.last_modified') }}: <span>@{{ selectedFile.last_modified_formated }}</span></h4>
                                </div>
                            </transition>

                            {{-- items count --}}
                            <transition-group class="__right-count" tag="div" name="slide" mode="out-in">
                                <div key="1" v-if="allItemsCount">
                                    <p class="title is-1">
                                        <bounty :value="allItemsCount"/>
                                    </p>
                                    <p class="heading">{{ trans('MediaManager::messages.total') }}</p>
                                </div>

                                <div key="2" v-if="searchItemsCount !== null && searchItemsCount >= 0">
                                    <p class="title is-1">
                                        <bounty :value="searchItemsCount"/>
                                    </p>
                                    <p class="heading">{{ trans('MediaManager::messages.found') }}</p>
                                </div>

                                <div key="3" v-if="bulkItemsCount">
                                    <p class="title is-1">
                                        <bounty :value="bulkItemsCount"/>
                                    </p>
                                    <p class="heading">{{ trans('MediaManager::messages.selected') }}</p>
                                </div>
                            </transition-group>
                        </div>
                    </div>
                </transition>
            </section>

            {{-- ====================================================================== --}}

            {{-- path toolbar --}}
            <section class="media-manager__stack-breadcrumb level is-mobile">
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

                <div class="level-right">
                    {{-- toggle info --}}
                    <div class="is-hidden-touch" @click="toggleInfoPanel()" v-tippy title="t" v-if="allItemsCount">
                        <transition :name="toggleInfo ? 'info-out' : 'info-in'" mode="out-in">
                            <div key="1" v-if="toggleInfo" class="__stack-right-toggle has-text-link">
                                <span>{{ trans('MediaManager::messages.close') }}</span>
                                <span class="icon"><icon name="angle-double-right"></icon></span>
                            </div>
                            <div key="2" v-else class="__stack-right-toggle has-text-link">
                                <span>{{ trans('MediaManager::messages.open') }}</span>
                                <span class="icon"><icon name="angle-double-left"></icon></span>
                            </div>
                        </transition>
                    </div>

                    {{-- show/hide toolbar --}}
                    <div class="is-hidden-desktop">
                        <button class="button is-link __stack-left-toolbarToggle" @click="toolBar = !toolBar">
                            <span class="icon"><icon :name="toolBar ? 'times' : 'bars'"></icon></span>
                        </button>
                    </div>
                </div>
            </section>
        </div>

        {{-- ====================================================================== --}}

        {{-- modals --}}
        <section>
            {{-- preview_modal --}}
            <div v-if="isActiveModal('preview_modal')"
                class="modal mm-animated fadeIn is-active __modal-preview">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="mm-animated fadeInDown">
                    <transition :name="navDirection == 'next' ? 'img-nxt' : 'img-prv'" mode="out-in">
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
                            <span class="icon"><icon name="folder"></icon></span>
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
                            <span class="icon"><icon name="i-cursor"></icon></span>
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
                            <span class="icon"><icon name="share"></icon></span>
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
                                    <icon name="search"></icon>
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
                            <span class="icon"><icon name="warning"></icon></span>
                            <span>{{ trans('MediaManager::messages.are_you_sure_delete') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.delete_file') }}" @submit.prevent="DeleteFileForm($event)">
                        <section class="modal-card-body">
                            <template v-if="bulkListFilter.length">
                                <table class="table" v-if="bulkListFilter.length <= 8">
                                    <tr class="__modal-delete-text" v-for="item in bulkListFilter">
                                        <td width="1%">
                                            <span class="icon is-large">
                                                <icon v-if="fileTypeIs(item, 'folder')" name="folder" scale="1.2"></icon>
                                                <icon v-if="fileTypeIs(item, 'image')" name="image" scale="1.2"></icon>
                                                <icon v-if="fileTypeIs(item, 'video')" name="film" scale="1.2"></icon>
                                                <icon v-if="fileTypeIs(item, 'audio')" name="music" scale="1.2"></icon>
                                                <icon v-if="fileTypeIs(item, 'pdf')" name="file-pdf-o" scale="1.2"></icon>
                                                <icon v-if="fileTypeIs(item, 'text')" name="file-text-o" scale="1.2"></icon>
                                            </span>
                                        </td>
                                        <td v-if="item.items" class="title is-5">@{{ item.name }} "@{{ item.items }} {{ trans('MediaManager::messages.items') }}"</td>
                                        <td v-else class="title is-5">@{{ item.name }}</td>
                                    </tr>
                                </table>

                                <p class="m-l-50 __modal-delete-text" v-else>
                                    <span class="icon is-large"><icon name="archive" scale="1.5"></icon></span>
                                    <span class="title is-5">"@{{ bulkListFilter.length }}" {{ trans('MediaManager::messages.too_many_files') }}</span>
                                </p>
                            </template>

                            <template v-else>
                                <table class="table" v-if="selectedFile">
                                    <tr class="__modal-delete-text">
                                        <td width="1%">
                                            <span class="icon is-large">
                                                <icon v-if="selectedFileIs('folder')" name="folder" scale="1.2"></icon>
                                                <icon v-if="selectedFileIs('image')" name="image" scale="1.2"></icon>
                                                <icon v-if="selectedFileIs('video')" name="film" scale="1.2"></icon>
                                                <icon v-if="selectedFileIs('audio')" name="music" scale="1.2"></icon>
                                                <icon v-if="selectedFileIs('pdf')" name="file-pdf-o" scale="1.2"></icon>
                                                <icon v-if="selectedFileIs('text')" name="file-text-o" scale="1.2"></icon>
                                            </span>
                                        </td>
                                        <td id="confirm_delete" class="title is-5" ref="confirm_delete"></td>
                                    </tr>
                                </table>
                            </template>

                            <h5 class="__modal-folder-warning" v-if="folderWarning">
                                <span class="icon"><icon name="warning"></icon></span>
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
<script src="//cdnjs.cloudflare.com/ajax/libs/bodymovin/4.13.0/bodymovin.min.js"></script>
<script src="{{ asset('assets/vendor/MediaManager/manager.js') }}"></script>
