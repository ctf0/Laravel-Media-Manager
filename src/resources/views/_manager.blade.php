{{-- component --}}
<media-manager inline-template v-cloak
    :config="{{ json_encode([
        'baseUrl' => $base_url, 
        'hideFilesExt' => config('mediaManager.hide_files_ext'), 
        'lazyLoad' => config('mediaManager.lazy_load_image_on_click'), 
        'mimeTypes' => config('mediaManager.extended_mimes'), 
        'cacheExp' => config('mediaManager.cache_expires_after'), 
        'broadcasting' => config('mediaManager.enable_broadcasting'), 
        'gfi' => config('mediaManager.get_folder_info'), 
        'ratioBar' => config('mediaManager.show_ratio_bar')
    ]) }}"
    :routes="{{ json_encode([
        'files' => route('media.files'), 
        'dirs' => route('media.directories'), 
        'lock' => route('media.lock_file'), 
        'visibility' => route('media.change_vis'), 
        'upload' => route('media.upload')
    ]) }}"
    :translations="{{ json_encode([
        'copied' => trans('MediaManager::messages.copy.copied'), 
        'copy_success' => trans('MediaManager::messages.copy.success'), 
        'create_folder_notif' => trans('MediaManager::messages.new.create_folder_notif'), 
        'create_success' => trans('MediaManager::messages.create_success'), 
        'delete_success' => trans('MediaManager::messages.delete.success'), 
        'downloaded' => trans('MediaManager::messages.download.downloaded'), 
        'error_altered_fwli' => trans('MediaManager::messages.error.altered_fwli'), 
        'find' => trans('MediaManager::messages.find'), 
        'found' => trans('MediaManager::messages.found'), 
        'glbl_search' => trans('MediaManager::messages.search.glbl'), 
        'glbl_search_avail' => trans('MediaManager::messages.search.glbl_avail'), 
        'go_to_folder' => trans('MediaManager::messages.go_to_folder'), 
        'move_success' => trans('MediaManager::messages.move.success'), 
        'new_uploads_notif' => trans('MediaManager::messages.upload.new_uploads_notif'), 
        'no_val' => trans('MediaManager::messages.no_val'), 
        'nothing_found' => trans('MediaManager::messages.nothing_found'), 
        'refresh_notif' => trans('MediaManager::messages.refresh_notif'), 
        'rename_success' => trans('MediaManager::messages.rename.success'), 
        'save_success' => trans('MediaManager::messages.save.success'), 
        'sep_download' => trans('MediaManager::messages.download.sep'), 
        'stand_by' => trans('MediaManager::messages.stand_by'), 
        'to_cp' => trans('MediaManager::messages.copy.to_cp'), 
        'upload_success' => trans('MediaManager::messages.upload.success'), 
    ]) }}"
    :in-modal="{{ isset($modal) ? 'true' : 'false' }}"
    :hide-ext="{{ isset($hideExt) ? json_encode($hideExt) : '[]' }}"
    :hide-path="{{ isset($hidePath) ? json_encode($hidePath) : '[]' }}"
    :restrict="{{ isset($restrict) ? json_encode($restrict) : '{}' }}"
    :user-id="{{ config('mediaManager.enable_broadcasting') ? auth()->user()->id : 0 }}"
    :upload-panel-img-list="{{ $patterns }}">

    <div class="">

        {{-- content ratio bar --}}
        <transition name="mm-list" mode="out-in">
            <content-ratio v-if="config.ratioBar && allItemsCount"
                :list="allFiles"
                :total="allItemsCount"
                :file-type-is="fileTypeIs">
            </content-ratio>
        </transition>

        {{-- global search --}}
        <global-search-panel
            :trans="trans"
            :file-type-is="fileTypeIs"
            :no-scroll="noScroll"
            :browser-support="browserSupport">
        </global-search-panel>

        {{-- usage-intro panel --}}
        <usage-intro-panel :no-scroll="noScroll"></usage-intro-panel>

        {{-- top toolbar --}}
        <transition name="mm-list" mode="out-in">
            <nav class="media-manager__toolbar level" v-show="toolBar">

                {{-- left toolbar --}}
                <div class="level-left">
                    {{-- first --}}
                    <div class="level-item">
                        <div class="field" :class="{'has-addons': !isBulkSelecting() && !restrictModeIsOn()}">
                            {{-- upload --}}
                            <div class="control" v-if="!isBulkSelecting()">
                                <button class="button"
                                    ref="upload"
                                    :disabled="isLoading"
                                    @click="toggleUploadPanel()"
                                    v-tippy
                                    title="u">
                                    <span class="icon"><icon name="shopping-basket"></icon></span>
                                    <span>{{ trans('MediaManager::messages.upload.main') }}</span>
                                </button>
                            </div>

                            {{-- new folder --}}
                            <div class="control" v-if="!restrictModeIsOn()">
                                <button class="button"
                                    :disabled="isLoading"
                                    @click="createNewFolder()">
                                    <span class="icon"><icon name="folder"></icon></span>
                                    <span>{{ trans('MediaManager::messages.add.folder') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- middle --}}
                    <div class="level-item">
                        <div class="field has-addons">
                            {{-- move --}}
                            <div class="control" v-if="!restrictModeIsOn()">
                                <button class="button is-link"
                                    ref="move"
                                    :disabled="item_ops() || !checkForFolders || isLoading"
                                    v-tippy
                                    title="m"
                                    @click="moveItem()">
                                    <span class="icon"><icon name="share" scale="0.8"></icon></span>
                                    <span>{{ trans('MediaManager::messages.move.main') }}</span>
                                </button>
                            </div>

                            {{-- rename --}}
                            <div class="control" v-if="!isBulkSelecting()">
                                <button class="button is-link"
                                    ref="rename"
                                    :disabled="item_ops() || isLoading"
                                    @click="renameItem()">
                                    <span class="icon"><icon name="terminal"></icon></span>
                                    <span>{{ trans('MediaManager::messages.rename.main') }}</span>
                                </button>
                            </div>

                            {{-- editor --}}
                            <div class="control" v-show="!isBulkSelecting()">
                                <button class="button is-link"
                                    ref="editor"
                                    :disabled="item_ops() || !selectedFileIs('image') || isLoading"
                                    v-tippy
                                    title="e"
                                    @click="imageEditor()">
                                    <span class="icon"><icon name="object-ungroup" scale="1.2"></icon></span>
                                    <span>{{ trans('MediaManager::messages.editor.main') }}</span>
                                </button>
                            </div>

                            {{-- delete --}}
                            <div class="control">
                                <button class="button is-link"
                                    ref="delete"
                                    :disabled="item_ops() || isLoading"
                                    v-tippy
                                    title="d / del"
                                    @click="deleteItem()">
                                    <span class="icon"><icon name="trash"></icon></span>
                                    <span>{{ trans('MediaManager::messages.delete.main') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- last --}}
                    <div class="level-item">
                        <div class="field has-addons">
                            {{-- refresh --}}
                            <div class="control" v-if="!isBulkSelecting()">
                                <v-touch class="button is-primary"
                                    ref="refresh"
                                    :disabled="isLoading"
                                    tag="button"
                                    v-tippy
                                    title="(R) efresh"
                                    @tap="refresh()"
                                    @hold="clearAll()">
                                    <span class="icon">
                                        <icon name="refresh" :spin="isLoading"></icon>
                                    </span>
                                </v-touch>
                            </div>

                            {{-- lock --}}
                            <div class="control">
                                <button class="button is-warning"
                                    ref="lock"
                                    :disabled="lock_btn()"
                                    v-tippy
                                    title="(L) ock"
                                    @click="lockFileForm()">
                                    <span class="icon">
                                        <icon :name="IsLocked(selectedFile) ? 'lock' : 'unlock'"></icon>
                                    </span>
                                </button>
                            </div>

                            {{-- visibility --}}
                            <div class="control">
                                <button class="button"
                                    :class="IsVisible(selectedFile) ? 'is-light' : 'is-danger'"
                                    ref="vis"
                                    :disabled="vis_btn()"
                                    v-tippy
                                    title="(V) isibility"
                                    @click="FileVisibilityForm()">
                                    <span class="icon">
                                        <icon :name="IsVisible(selectedFile) ? 'eye' : 'eye-slash'"></icon>
                                    </span>
                                </button>
                            </div>
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
                                    :disabled="searchItemsCount == 0 || isLoading"
                                    v-tippy
                                    title="a">
                                    <template v-if="bulkSelectAll">
                                        <span class="icon"><icon name="minus" scale="0.8"></icon></span>
                                        <span>{{ trans('MediaManager::messages.select.non') }}</span>
                                    </template>
                                    <template v-else>
                                        <span class="icon"><icon name="plus" scale="0.8"></icon></span>
                                        <span>{{ trans('MediaManager::messages.select.all') }}</span>
                                    </template>
                                </button>
                            </div>

                            {{-- bulk select --}}
                            <div class="control">
                                <button @click="blkSlct()"
                                    ref="bulkSelect"
                                    class="button"
                                    :class="{'is-danger' : bulkSelect}"
                                    :disabled="searchItemsCount == 0 || !allItemsCount || isLoading"
                                    v-tippy
                                    title="b">
                                    <span class="icon"><icon name="puzzle-piece"></icon></span>
                                    <span>{{ trans('MediaManager::messages.select.bulk') }}</span>
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
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.image')]) }}"
                                            class="button"
                                            :class="{'is-link': filterNameIs('image')}"
                                            :disabled="!btnFilter('image') || isLoading">
                                            <span class="icon"><icon name="image"></icon></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('video')"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.video')]) }}"
                                            class="button"
                                            :class="{'is-link': filterNameIs('video')}"
                                            :disabled="!btnFilter('video') || isLoading">
                                            <span class="icon"><icon name="video-camera"></icon></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('audio')"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.audio.main')]) }}"
                                            class="button"
                                            :class="{'is-link': filterNameIs('audio')}"
                                            :disabled="!btnFilter('audio') || isLoading">
                                            <span class="icon"><icon name="music"></icon></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('folder')"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.folder')]) }}"
                                            class="button"
                                            :class="{'is-link': filterNameIs('folder')}"
                                            :disabled="!btnFilter('folder') || isLoading">
                                            <span class="icon"><icon name="folder"></icon></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('text')"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.text')]) }}"
                                            class="button"
                                            :class="{'is-link': filterNameIs('text')}"
                                            :disabled="!btnFilter('text') || isLoading">
                                            <span class="icon"><icon name="file-text-o"></icon></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('application')"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.application')]) }}"
                                            class="button"
                                            :class="{'is-link': filterNameIs('application')}"
                                            :disabled="!btnFilter('application') || isLoading">
                                            <span class="icon"><icon name="cogs"></icon></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('locked')"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.locked')]) }}"
                                            class="button"
                                            :class="{'is-link': filterNameIs('locked')}"
                                            :disabled="!btnFilter('locked') || isLoading">
                                            <span class="icon"><icon name="key"></icon></span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <button @click="showFilesOfType('selected')"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.select.selected')]) }}"
                                            class="button"
                                            :class="{'is-link': filterNameIs('selected')}"
                                            :disabled="!btnFilter('selected') || isLoading">
                                            <span class="icon"><icon name="check"></icon></span>
                                        </button>
                                    </div>
                                    {{-- clear --}}
                                    <div class="control">
                                        <button @click="showFilesOfType('all')"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.clear', ['attr' => trans('MediaManager::messages.filter')]) }}"
                                            class="button"
                                            :class="{'is-danger': btnFilter('all')}"
                                            :disabled="!btnFilter('all') || isLoading">
                                            <span class="icon"><icon name="times"></icon></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- sort by --}}
                        <div class="level-item" v-show="searchItemsCount != 0">
                            <div class="control has-icons-left">
                                <div class="select">
                                    <select v-model="sortBy" :disabled="isLoading">
                                        <option disabled value="null">{{ trans('MediaManager::messages.sort_by') }}</option>
                                        <option value="clear">{{ trans('MediaManager::messages.non') }}</option>
                                        <option value="size">{{ trans('MediaManager::messages.size') }}</option>
                                        <option value="last_modified">{{ trans('MediaManager::messages.last_modified') }}</option>
                                    </select>
                                </div>
                                <div class="icon is-left">
                                    <icon name="bell-o"></icon>
                                </div>
                            </div>
                        </div>

                        {{-- search --}}
                        <div class="level-item">
                            <div class="control">
                                <div class="field has-addons">
                                    <p class="control" v-if="!restrictModeIsOn()">
                                        <global-search-btn
                                            route="{{ route('media.global_search') }}"
                                            :is-loading="isLoading"
                                            :trans="trans"
                                            :show-notif="showNotif">
                                        </global-search-btn>
                                    </p>

                                    <p class="control has-icons-left">
                                        <input class="input"
                                            :disabled="isLoading"
                                            type="text"
                                            ref="search"
                                            v-model="searchFor"
                                            :placeholder="trans('find')">
                                        <span class="icon is-left">
                                            <icon name="search"></icon>
                                        </span>
                                    </p>

                                    <p class="control">
                                        <button class="button is-black" :disabled="!searchFor"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.clear', ['attr' => trans('MediaManager::messages.search.main')]) }}"
                                            @click="resetInput('searchFor')" >
                                            <span class="icon"><icon name="times"></icon></span>
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </nav>
        </transition>

        {{-- ====================================================================== --}}

        {{-- dropzone --}}
        <section>
            <div class="media-manager__dz" :class="{'__dz-active': UploadArea}">
                <div id="new-upload" :style="uploadPanelImg">
                    {{-- text --}}
                    <div class="dz-message title is-4">{!! trans('MediaManager::messages.upload.text') !!}</div>

                    {{-- randomNames --}}
                    <div class="form-switcher"
                        title="{{ trans('MediaManager::messages.upload.use_random_names') }}"
                        v-tippy="{arrow: true, position: 'right'}">
                        <input type="checkbox" id="random_names" v-model="randomNames">
                        <label class="switcher" for="random_names"></label>
                    </div>

                    {{-- urlToUpload --}}
                    <div class="save_link" @click="toggleModal('save_link_modal')" v-if="!restrictUpload()">
                        <span class="icon is-large"
                            title="{{ trans('MediaManager::messages.save.link') }}"
                            v-tippy="{arrow: true, position: 'left'}">
                            <icon>
                                <icon class="circle" name="circle" scale="2.5"></icon>
                                <icon class="anchor" name="link"></icon>
                            </icon>
                        </span>
                    </div>
                </div>

                <div id="uploadPreview"></div>
            </div>

            <transition name="mm-list">
                <div v-show="showProgress" id="uploadProgress" class="progress">
                    <div class="progress-bar is-success progress-bar-striped active" :style="{width: progressCounter}"></div>
                </div>
            </transition>
        </section>

        {{-- ====================================================================== --}}

        {{-- mobile breadCrumb --}}
        @include('MediaManager::partials.mobile-nav')

        {{-- ====================================================================== --}}

        <div class="media-manager__stack">
            <section class="__stack-container">

                {{-- loadings --}}
                <section>
                    {{-- loading data from server --}}
                    <div id="loading_files" v-show="loading_files">
                        <div id="loading_files_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/world.json') }}"></div>

                        <transition name="mm-list" mode="out-in">
                            <h3 key="1" v-if="showProgress" class="mm-animated pulse">
                                {{ trans('MediaManager::messages.stand_by') }}
                                <strong>@{{ progressCounter }}</strong>
                            </h3>
                            <h3 key="2" v-else>{{ trans('MediaManager::messages.loading') }}</h3>
                        </transition>
                    </div>

                    {{-- ajax error --}}
                    <div id="ajax_error" v-show="ajax_error">
                        <div id="ajax_error_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/avalanche.json') }}"></div>
                        <h3>{{ trans('MediaManager::messages.ajax_error') }}</h3>
                    </div>

                    {{-- no files --}}
                    <v-touch id="no_files"
                        v-show="no_files"
                        class="no_files"
                        @swiperight="goToPrevFolder($event, 'no_files')"
                        @swipeleft="goToPrevFolder($event, 'no_files')"
                        @hold="containerClick($event, 'no_files')"
                        @dbltap="containerClick($event, 'no_files')">
                        <div id="no_files_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/zero.json') }}"></div>
                        <h3>{{ trans('MediaManager::messages.no_files_in_folder') }}</h3>
                    </v-touch>

                    {{-- gesture --}}
                    <overlay></overlay>
                </section>

                {{-- usage-intro btn --}}
                <usage-intro-btn v-show="!isLoading"></usage-intro-btn>

                {{-- ====================================================================== --}}

                {{-- files box --}}
                <v-touch class="__stack-files mm-animated"
                    :class="{'__stack-sidebar-hidden' : !infoSidebar}"
                    ref="__stack-files"
                    @swiperight="goToPrevFolder($event, '__stack-files')"
                    @swipeleft="goToPrevFolder($event, '__stack-files')"
                    @hold="containerClick($event)"
                    @dbltap="containerClick($event)"
                    @pinchin="containerClick($event)">

                    {{-- no search --}}
                    <section>
                        <div id="no_search" v-show="no_search">
                            <div id="no_search_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/ice_cream.json') }}"></div>
                            <h3>@{{ trans('nothing_found') }}</h3>
                        </div>
                    </section>

                    {{-- files --}}
                    <ul class="__files-boxs" ref="filesList">
                        <li v-for="(file, index) in orderBy(filterBy(allFiles, searchFor, 'name'), sortBy, -1)"
                            :key="file.name"
                            :data-file-index="index"
                            @click="setSelected(file, index, $event)">
                            <v-touch class="__file-box mm-animated"
                                :class="{'bulk-selected': IsInBulkList(file), 'selected' : isSelected(file)}"
                                @swipeup="swipGesture($event, file, index)"
                                @swipedown="swipGesture($event, file, index)"
                                @swiperight="swipGesture($event, file, index)"
                                @swipeleft="swipGesture($event, file, index)"
                                @hold="pressGesture($event, file, index)"
                                @dbltap="pressGesture($event, file, index)">

                                {{-- lock file --}}
                                <button v-if="$refs.lock"
                                    class="__box-lock-icon icon"
                                    :disabled="isLoading"
                                    :class="IsLocked(file) ? 'is-danger' : 'is-success'"
                                    :title="IsLocked(file) ? '{{ trans('MediaManager::messages.unlock') }}': '{{ trans('MediaManager::messages.lock') }}'"
                                    v-tippy="{arrow: true, hideOnClick: false}"
                                    @click.stop="$refs.lock.click()">
                                </button>

                                {{-- copy file link --}}
                                <div v-if="!fileTypeIs(file, 'folder')"
                                    class="__box-copy-link icon"
                                    @click.stop="copyLink(file.path)"
                                    :title="linkCopied ? trans('copied') : trans('to_cp')"
                                    v-tippy="{arrow: true, hideOnClick: false}"
                                    @hidden="linkCopied = false">
                                    <icon name="clone" scale="0.9"></icon>
                                </div>

                                <div class="__box-data">
                                    <div class="__box-preview">
                                        {{-- get video dimensions --}}
                                        <video-dimension v-if="fileTypeIs(file, 'video')"
                                            class="is-hidden"
                                            :file="file">
                                        </video-dimension>

                                        <template v-if="fileTypeIs(file, 'image')">
                                            <image-cache v-if="config.lazyLoad"
                                                :file="file"
                                                :db="CDBN"
                                                :browser-support="browserSupport">
                                            </image-cache>
                                            <image-intersect v-else
                                                :file="file"
                                                :browser-support="browserSupport"
                                                root-el=".__stack-files">
                                            </image-intersect>
                                        </template>

                                        <span v-else class="icon is-large">
                                            <icon v-if="fileTypeIs(file, 'folder')" name="folder" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'application')" name="cogs" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'compressed')" name="file-archive-o" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'video')" name="film" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'audio')" name="music" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'pdf')" name="file-pdf-o" scale="2.6"></icon>
                                            <icon v-else-if="fileTypeIs(file, 'text')" name="file-text-o" scale="2.6"></icon>
                                        </span>
                                    </div>

                                    <div class="__box-info">
                                        {{-- folder --}}
                                        <template v-if="fileTypeIs(file, 'folder')">
                                            <h4>@{{ file.name }}</h4>
                                            <small>
                                                <span>@{{ file.count }} {{ trans('MediaManager::messages.items') }}</span>
                                                <span v-if="file.size > 0" class="__info-file-size">"@{{ getFileSize(file.size) }}"</span>
                                            </small>
                                        </template>

                                        {{-- any other --}}
                                        <template v-else>
                                            <h4>@{{ getFileName(file.name) }}</h4>
                                            <small class="__info-file-size">@{{ getFileSize(file.size) }}</small>
                                        </template>
                                    </div>
                                </div>
                            </v-touch>
                        </li>
                    </ul>
                </v-touch>

                {{-- ====================================================================== --}}

                {{-- info sidebar --}}
                <v-touch v-if="infoSidebar"
                        class="__stack-sidebar is-hidden-touch"
                        @swiperight="toggleInfoSidebar(), saveUserPref()"
                        @swipeleft="toggleInfoSidebar(), saveUserPref()">

                        {{-- preview --}}
                        <div class="__sidebar-preview">
                            <transition name="mm-slide" mode="out-in" appear
                                v-on:after-enter="isScrollable()">

                                {{-- no selection --}}
                                <div key="none-selected" class="__sidebar-none-selected" v-if="!selectedFile">
                                    <span @click="reset()" class="link"><icon name="power-off" scale="3.2"></icon></span>
                                    <p>{{ trans('MediaManager::messages.select.nothing') }}</p>
                                </div>

                                {{-- img --}}
                                <div v-else-if="selectedFileIs('image')"
                                    v-tippy="{arrow: true, position: 'left'}"
                                    title="space"
                                    :key="selectedFile.name"
                                    class="image-wrapper">
                                    <div ref="img-prev" @scroll="updateScrollableDir('img-prev')">

                                        <img :src="selectedFilePreview"
                                            :alt="selectedFile.name"
                                            class="link image"
                                            @click="isBulkSelecting() ? false : toggleModal('preview_modal')"/>
                                    </div>

                                    <transition :name="scrollableBtn.state ? 'mm-img-nxt': 'mm-img-prv'" appear>
                                        <div class="image-scroll-btn"
                                            :class="scrollableBtn.dir"
                                            v-show="scrollableBtn.state"
                                            @click="scrollImg('img-prev')">
                                            <span class="icon is-large"><icon name="chevron-down" scale="1"></icon></span>
                                        </div>
                                    </transition>
                                </div>

                                {{-- video --}}
                                <div v-else-if="selectedFileIs('video')"
                                    v-tippy="{arrow: true, position: 'left'}"
                                    title="space"
                                    :key="selectedFile.name">
                                    <video controls
                                        playsinline
                                        preload="metadata"
                                        data-player
                                        :src="selectedFile.path">
                                        {{ trans('MediaManager::messages.video_support') }}
                                    </video>
                                </div>

                                {{-- audio --}}
                                <div v-else-if="selectedFileIs('audio')"
                                    v-tippy="{arrow: true, position: 'left'}"
                                    title="space"
                                    :key="selectedFile.name">
                                    <template>
                                        <img v-if="selectedFilePreview && selectedFilePreview.picture"
                                            :src="selectedFilePreview.picture"
                                            :alt="selectedFile.name"
                                            class="image"/>

                                        <icon v-else class="svg-prev-icon" name="music" scale="8"></icon>
                                    </template>

                                    <audio controls
                                        class="is-hidden"
                                        preload="metadata"
                                        data-player
                                        :src="selectedFile.path">
                                        {{ trans('MediaManager::messages.audio.support') }}
                                    </audio>
                                </div>

                                {{-- icons --}}
                                <icon key="1"
                                    class="svg-prev-icon"
                                    v-else-if="selectedFileIs('folder')"
                                    name="folder" scale="4">
                                </icon>

                                <icon key="2"
                                    class="svg-prev-icon"
                                    v-else-if="selectedFileIs('application')"
                                    name="cogs" scale="4">
                                </icon>

                                <div key="3" v-else-if="selectedFileIs('pdf')"
                                    class="link"
                                    v-tippy="{arrow: true, position: 'left'}"
                                    title="space"
                                    @click="toggleModal('preview_modal')">
                                    <icon class="svg-prev-icon" name="file-pdf-o" scale="4"></icon>
                                </div>

                                <div key="4" v-else-if="selectedFileIs('text')"
                                    class="link"
                                    v-tippy="{arrow: true, position: 'left'}"
                                    title="space"
                                    @click="toggleModal('preview_modal')">
                                    <icon class="svg-prev-icon" name="file-text-o" scale="4"></icon>
                                </div>

                                <icon key="5"
                                    class="svg-prev-icon"
                                    v-else-if="selectedFileIs('compressed')"
                                    name="file-archive-o" scale="4">
                                </icon>
                            </transition>
                        </div>

                        {{-- info --}}
                        <div v-if="allItemsCount"
                            class="__sidebar-info"
                            :style="{'background-color': selectedFile ? 'white' : ''}">

                            <transition name="mm-list" mode="out-in" appear>
                                <div :key="selectedFile.name" v-if="selectedFile">
                                    {{-- audio extra info --}}
                                    <template v-if="selectedFileIs('audio') && selectedFilePreview">
                                        <table>
                                            <tr v-if="selectedFilePreview.artist">
                                                <td class="t-key">{{ trans('MediaManager::messages.audio.artist') }}:</td>
                                                <td class="t-val">@{{ selectedFilePreview.artist }}</td>
                                            </tr>
                                            <tr v-if="selectedFilePreview.title">
                                                <td class="t-key">{{ trans('MediaManager::messages.audio.title') }}:</td>
                                                <td class="t-val">@{{ selectedFilePreview.title }}</td>
                                            </tr>
                                            <tr v-if="selectedFilePreview.album">
                                                <td class="t-key">{{ trans('MediaManager::messages.audio.album') }}:</td>
                                                <td class="t-val">@{{ selectedFilePreview.album }}</td>
                                            </tr>
                                            <tr v-if="selectedFilePreview.track">
                                                <td class="t-key">{{ trans('MediaManager::messages.audio.track') }}:</td>
                                                <td class="t-val">@{{ selectedFilePreview.track }}</td>
                                            </tr>
                                            <tr v-if="selectedFilePreview.year">
                                                <td class="t-key">{{ trans('MediaManager::messages.audio.year') }}:</td>
                                                <td class="t-val">@{{ selectedFilePreview.year }}</td>
                                            </tr>
                                        </table>
                                        <hr class="m-v-10">
                                    </template>

                                    <table>
                                        <tr>
                                            <td class="t-key">{{ trans('MediaManager::messages.name') }}:</td>
                                            <td class="t-val">@{{ selectedFile.name }}</td>
                                        </tr>
                                    </table>
                                    <table>
                                        <tr>
                                            <td class="t-key">{{ trans('MediaManager::messages.type') }}:</td>
                                            <td class="t-val">@{{ selectedFile.type }}</td>
                                        </tr>
                                    </table>
                                    <table>
                                        <tr>
                                            <td class="t-key">{{ trans('MediaManager::messages.size') }}:</td>
                                            <td class="t-val">@{{ getFileSize(selectedFile.size) }}</td>
                                        </tr>
                                    </table>

                                    {{-- folder --}}
                                    <template v-if="selectedFileIs('folder')">
                                        <table>
                                            <tr>
                                                <td class="t-key">{{ trans('MediaManager::messages.items') }}:</td>
                                                <td class="t-val">@{{ selectedFile.count }}</td>
                                            </tr>
                                        </table>

                                        <div class="__sidebar-zip" v-show="!isBulkSelecting()">
                                            <table>
                                                <tr>
                                                    <td class="t-key">{{ trans('MediaManager::messages.download.folder') }}:</td>
                                                    <td class="t-val">
                                                        <form action="{{ route('media.folder_download') }}" method="post" @submit.prevent="ZipDownload($event)">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="folders" :value="folders.length ? '/' + folders.join('/') : null">
                                                            <input type="hidden" name="name" :value="selectedFile.name">
                                                            <button type="submit" class="btn-plain zip" :disabled="config.gfi && selectedFile.count == 0">
                                                                <span class="icon"><icon name="archive" scale="1.2"></icon></span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </template>

                                    {{-- file --}}
                                    <template v-else>
                                        <table v-if="(selectedFileIs('image') || selectedFileIs('video')) && dimensions.length">
                                            <tr>
                                                <td class="t-key">{{ trans('MediaManager::messages.dimension') }}:</td>
                                                <td class="t-val">@{{ selectedFileDimensions }}</td>
                                            </tr>
                                        </table>
                                        <table>
                                            <tr>
                                                <td class="t-key">{{ trans('MediaManager::messages.visibility.main') }}:</td>
                                                <td class="t-val">@{{ selectedFile.visibility }}</td>
                                            </tr>
                                        </table>
                                        <table>
                                            <tr>
                                                <td class="t-key">{{ trans('MediaManager::messages.preview') }}:</td>
                                                <td class="t-val"><a :href="selectedFile.path" target="_blank">{{ trans('MediaManager::messages.public_url') }}</a></td>
                                            </tr>
                                        </table>

                                        <div class="__sidebar-zip">
                                            <table>
                                                <tr>
                                                    <td class="t-key">{{ trans('MediaManager::messages.download.file') }}:</td>
                                                    <td class="t-val">
                                                        {{-- normal --}}
                                                        <button class="btn-plain" @click.prevent="saveFile(selectedFile)">
                                                            <span class="icon"><icon name="download" scale="1.2"></icon></span>
                                                        </button>
                                                    </td>
                                                    <td class="t-val">
                                                        {{-- zip --}}
                                                        <form action="{{ route('media.files_download') }}" method="post" @submit.prevent="ZipDownload($event)" v-show="isBulkSelecting()">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="list" :value="JSON.stringify(bulkList)">
                                                            <input type="hidden" name="name" :value="folders.length ? folders[folders.length - 1] : 'media_manager'">
                                                            <button type="submit" class="btn-plain zip" :disabled="hasFolder()">
                                                                <span class="icon"><icon name="archive" scale="1.2"></icon></span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </template>

                                    <table>
                                        <tr>
                                            <td class="t-key">{{ trans('MediaManager::messages.last_modified') }}:</td>
                                            <td class="t-val">@{{ selectedFile.last_modified_formated }}</td>
                                        </tr>
                                    </table>
                                </div>

                                {{-- keep the counts at bottom --}}
                                <div v-else></div>
                            </transition>

                            {{-- items count --}}
                            <transition-group tag="div" name="mm-counter" mode="out-in" class="__sidebar-count">
                                {{-- all --}}
                                <div key="1" v-if="allItemsCount">
                                    <p class="title is-1">@{{ allItemsCount }}</p>
                                    <p class="heading">{{ trans('MediaManager::messages.total') }}</p>
                                </div>

                                {{-- search --}}
                                <div key="2" v-if="searchItemsCount !== null && searchItemsCount >= 0">
                                    <p class="title is-1">@{{ searchItemsCount }}</p>
                                    <p class="heading">@{{ trans('found') }}</p>
                                </div>

                                {{-- bulk --}}
                                <div key="3" v-if="bulkItemsCount" class="__sidebar-count-bulk">
                                    <p>
                                        <span class="title is-1">@{{ bulkItemsCount }}</span>

                                        {{-- nested --}}
                                        <template v-if="bulkItemsChild">
                                            <span class="icon is-medium"><icon name="plus"></icon></span>
                                            <span class="title is-5">@{{ bulkItemsChild }}</span>
                                        </template>
                                    </p>

                                    {{-- size --}}
                                    <p v-show="bulkItemsSize" class="title is-6 has-text-weight-semibold">@{{ bulkItemsSize }}</p>
                                    <p class="heading">{{ trans('MediaManager::messages.select.selected') }}</p>
                                </div>
                            </transition-group>
                        </div>
                </v-touch>

                <v-touch v-else-if="!infoSidebar && !smallScreen"
                    class="__sidebar-swipe-hidden"
                    @swiperight="toggleInfoSidebar(), saveUserPref()"
                    @swipeleft="toggleInfoSidebar(), saveUserPref()">
                </v-touch>

            </section>

            {{-- ====================================================================== --}}

            {{-- path toolbar --}}
            <section class="__stack-breadcrumb level is-mobile">
                {{-- directories breadCrumb --}}
                <div class="level-left">
                    <nav class="breadcrumb has-arrow-separator is-hidden-touch" v-if="!restrictModeIsOn()">
                        <transition-group tag="ul" name="mm-list" mode="out-in">
                            <li key="library-bc">
                                <a v-if="folders.length > 0 && !(isBulkSelecting() || isLoading)"
                                    class="p-l-0 level"
                                    v-tippy
                                    title="backspace"
                                    @click="goToFolder(0)">
                                    <span class="icon level-item is-marginless"><icon name="map"></icon></span>
                                    <span class="level-item m-l-5 is-marginless">{{ trans('MediaManager::messages.library') }}</span>
                                </a>
                                <p v-else class="p-l-0 level">
                                    <span class="icon level-item is-marginless"><icon name="map-o"></icon></span>
                                    <span class="level-item m-l-5 is-marginless">{{ trans('MediaManager::messages.library') }}</span>
                                </p>
                            </li>

                            <li v-for="(folder, index) in folders" :key="index">
                                <p v-if="isLastItem(folder, folders) || isBulkSelecting() || isLoading"
                                    class="level">
                                    <span class="icon level-item is-marginless"><icon name="folder-open-o"></icon></span>
                                    <span class="level-item m-l-5 is-marginless">@{{ folder }}</span>
                                </p>
                                <a v-else
                                    v-tippy
                                    title="backspace"
                                    class="level"
                                    @click="folders.length > 1 ? goToFolder(index+1) : false">
                                    <span class="icon level-item is-marginless"><icon name="folder"></icon></span>
                                    <span class="level-item m-l-5 is-marginless">@{{ folder }}</span>
                                </a>
                            </li>
                        </transition-group>
                    </nav>
                </div>

                {{-- toggle sidebar --}}
                <div class="level-right" v-show="!isLoading">
                    <div class="is-hidden-touch"
                        @click="toggleInfoSidebar(), saveUserPref()"
                        v-tippy
                        title="t"
                        v-if="allItemsCount">
                        <transition :name="infoSidebar ? 'mm-info-out' : 'mm-info-in'" mode="out-in">
                            <div :key="infoSidebar ? 1 : 2" class="__stack-sidebar-toggle has-text-link">
                                <template v-if="infoSidebar">
                                    <span>{{ trans('MediaManager::messages.close') }}</span>
                                    <span class="icon"><icon name="angle-double-right"></icon></span>
                                </template>
                                <template v-else>
                                    <span>{{ trans('MediaManager::messages.open') }}</span>
                                    <span class="icon"><icon name="angle-double-left"></icon></span>
                                </template>
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
                <div class="mm-animated fadeInDown __modal-content-wrapper">
                    <transition :name="`mm-img-${imageSlideDirection}`"
                        mode="out-in" appear
                        v-on:after-enter="isScrollable()"
                        v-on:before-leave="scrollableBtn.state = false">

                        <div class="modal-content" :key="selectedFile.path">
                            {{-- card v --}}
                            @include('MediaManager::partials.card')
                        </div>
                    </transition>
                </div>
                <button class="modal-close is-large" @click="toggleModal()"></button>
            </div>

            {{-- image_editor --}}
            <div v-if="isActiveModal('imageEditor_modal')"
                class="modal mm-animated fadeIn is-active __modal-editor">
                <v-touch class="modal-background link" @dbltap="toggleModal()"></v-touch>
                <div class="mm-animated fadeInDown __modal-content-wrapper">
                    <image-editor route="{{ route('media.uploadCropped') }}"
                        :file="selectedFile"
                        :url="selectedFilePreview"
                        :translations="{{ json_encode([
                            'clear' => trans('MediaManager::messages.clear', ['attr' => 'selection']), 
                            'move' => trans('MediaManager::messages.move.main'), 
                            'save_success' => trans('MediaManager::messages.save.success'), 
                            'diff' => trans('MediaManager::messages.editor.diff'), 
                            'presets' => trans('MediaManager::messages.crop.presets'), 
                            'crop' => trans('MediaManager::messages.crop.main'), 
                            'crop_reset' => trans('MediaManager::messages.crop.reset'), 
                            'crop_reset_filters' => trans('MediaManager::messages.crop.reset_filters'), 
                            'crop_apply' => trans('MediaManager::messages.crop.apply'), 
                            'crop_zoom_in' => trans('MediaManager::messages.crop.zoom_in'), 
                            'crop_zoom_out' => trans('MediaManager::messages.crop.zoom_out'), 
                            'crop_rotate_left' => trans('MediaManager::messages.crop.rotate_left'), 
                            'crop_rotate_right' => trans('MediaManager::messages.crop.rotate_right'), 
                            'crop_flip_horizontal' => trans('MediaManager::messages.crop.flip_horizontal'), 
                            'crop_flip_vertical' => trans('MediaManager::messages.crop.flip_vertical'), 
                        ]) }}">
                    </image-editor>
                </div>
                <button class="modal-close is-large" @click="toggleModal()"></button>
            </div>

            {{-- save_link --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('save_link_modal')}">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-black">
                        <p class="modal-card-title">
                            <span>{{ trans('MediaManager::messages.save.link') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.uploadLink') }}" @submit.prevent="saveLinkForm($event)">
                        <section class="modal-card-body">
                            <input class="input" type="text"
                                v-model="urlToUpload"
                                placeholder="{{ trans('MediaManager::messages.add.url') }}"
                                ref="save_link_modal_input">
                        </section>
                        <footer class="modal-card-foot">
                            <button type="reset" class="button" @click="toggleModal()">
                                {{ trans('MediaManager::messages.cancel') }}
                            </button>
                            <button type="submit"
                                class="button is-link"
                                :disabled="isLoading"
                                :class="{'is-loading': isLoading}">
                                {{ trans('MediaManager::messages.upload.main') }}
                            </button>
                        </footer>
                    </form>
                </div>
            </div>

            {{-- new_folder_modal --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('new_folder_modal')}">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-link">
                        <p class="modal-card-title">
                            <span>{{ trans('MediaManager::messages.add.new_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.new_folder') }}" @submit.prevent="NewFolderForm($event)">
                        <section class="modal-card-body">
                            <input class="input" type="text"
                                v-model="newFolderName"
                                placeholder="{{ trans('MediaManager::messages.new.folder_name') }}"
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
                                {{ trans('MediaManager::messages.new.create_folder') }}
                            </button>
                        </footer>
                    </form>
                </div>
            </div>

            {{-- rename_file_modal --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('rename_file_modal')}">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-warning">
                        <p class="modal-card-title">
                            <span>{{ trans('MediaManager::messages.rename.file_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.rename_file') }}" @submit.prevent="RenameFileForm($event)">
                        <section class="modal-card-body">
                            <h3 class="title">{{ trans('MediaManager::messages.new.file_folder') }}</h3>
                            <input class="input" type="text"
                                v-if="selectedFile"
                                v-model="newFilename"
                                ref="rename_file_modal_input"
                                @focus="newFilename = selectedFileIs('folder') ? selectedFile.name : getFileName(selectedFile.name)">
                        </section>
                        <footer class="modal-card-foot">
                            <button type="reset" class="button" @click="toggleModal()">
                                {{ trans('MediaManager::messages.cancel') }}
                            </button>
                            <button type="submit"
                                class="button is-warning"
                                :disabled="isLoading"
                                :class="{'is-loading': isLoading}">
                                {{ trans('MediaManager::messages.rename.main') }}
                            </button>
                        </footer>
                    </form>
                </div>
            </div>

            {{-- move_file_modal --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('move_file_modal')}">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-warning">
                        <p class="modal-card-title">
                            <transition :name="useCopy ? 'mm-info-in' : 'mm-info-out'" mode="out-in">
                                <span class="icon" :key="useCopy ? 1 : 2">
                                    <icon :name="useCopy ? 'clone' : 'share'"></icon>
                                </span>
                            </transition>

                            <transition name="mm-list" mode="out-in">
                                <span key="1" v-if="useCopy">{{ trans('MediaManager::messages.copy.file_folder') }}</span>
                                <span key="2" v-else>{{ trans('MediaManager::messages.move.file_folder') }}</span>
                            </transition>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.move_file') }}" @submit.prevent="MoveFileForm($event)">
                        <section class="modal-card-body">
                            {{-- destination --}}
                            <h5 class="subtitle m-b-10">{{ trans('MediaManager::messages.destination_folder') }}</h5>
                            <div class="field">
                                <div class="control has-icons-left">
                                    <span class="select is-fullwidth">
                                        <select ref="move_folder_dropdown" v-model="moveToPath">
                                            <option v-if="moveUpCheck()" value="../">../</option>
                                            <option v-for="(dir, index) in filterDirList()"
                                                :key="index"
                                                :value="dir">
                                                @{{ dir }}
                                            </option>
                                        </select>
                                    </span>
                                    <span class="icon is-left">
                                        <icon name="search"></icon>
                                    </span>
                                </div>
                            </div>

                            <br>
                            @include('MediaManager::partials.modal-files-info')
                        </section>

                        <footer class="modal-card-foot">
                            {{-- switcher --}}
                            <div class="level is-mobile full-width">
                                <div class="level-left">
                                    <div class="level-item">
                                        <div class="form-switcher">
                                            <input type="checkbox" name="use_copy" id="use_copy" v-model="useCopy">
                                            <label class="switcher" for="use_copy"></label>
                                        </div>
                                    </div>
                                    <div class="level-item">
                                        <label class="label" for="use_copy">{{ trans('MediaManager::messages.copy.files') }}</label>
                                    </div>
                                </div>

                                <div class="level-right">
                                    <div class="level-item">
                                        <button type="reset" class="button" @click="toggleModal()">
                                            {{ trans('MediaManager::messages.cancel') }}
                                        </button>
                                    </div>
                                    <div class="level-item">
                                        <button type="submit"
                                            class="button is-warning"
                                            :disabled="isLoading || !moveToPath"
                                            :class="{'is-loading': isLoading}">
                                            <transition :name="useCopy ? 'mm-img-prv' : 'mm-img-nxt'" mode="out-in">
                                                <span key="1" v-if="useCopy">{{ trans('MediaManager::messages.copy.main') }}</span>
                                                <span key="2" v-else>{{ trans('MediaManager::messages.move.main') }}</span>
                                            </transition>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </footer>
                    </form>
                </div>
            </div>

            {{-- confirm_delete_modal --}}
            <div class="modal mm-animated fadeIn"
                :class="{'is-active': isActiveModal('confirm_delete_modal')}">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-danger">
                        <p class="modal-card-title">
                            <span>{{ trans('MediaManager::messages.are_you_sure_delete') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.delete_file') }}" @submit.prevent="DeleteFileForm($event)">
                        <section class="modal-card-body">
                            @include('MediaManager::partials.modal-files-info')

                            {{-- deleting folder warning --}}
                            <h5 v-show="folderWarning" class="__modal-folder-warning">
                                <span class="icon is-medium"><icon name="warning" scale="1.2"></icon></span>
                                <span>{{ trans('MediaManager::messages.delete.folder') }}</span>
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
                                {{ trans('MediaManager::messages.delete.confirm') }}
                            </button>
                        </footer>
                    </form>
                </div>
            </div>
        </section>

    </div>
</media-manager>

{{-- styles --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/MediaManager/style.css') }}"/>
@endpush

{{-- scripts --}}
@push('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/camanjs/4.1.2/caman.full.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jsmediatags/3.9.0/jsmediatags.min.js"></script>
@endpush
