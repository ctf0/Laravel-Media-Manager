{{-- component --}}
<media-manager inline-template
    :config="{{ json_encode([
        'baseUrl' => $base_url, 
        'hideFilesExt' => config('mediaManager.hide_files_ext'), 
        'lazyLoad' => config('mediaManager.lazy_load_image_on_click'), 
        'imageTypes' => config('mediaManager.image_extended_mimes'), 
        'cacheExp' => config('mediaManager.cacheExpiresAfter'), 
    ]) }}"
    :in-modal="{{ isset($modal) ? 'true' : 'false' }}"
    :hide-ext="{{ isset($hideExt) ? json_encode($hideExt) : '[]' }}"
    :hide-path="{{ isset($hidePath) ? json_encode($hidePath) : '[]' }}"
    :restrict="{{ isset($restrict) ? json_encode($restrict) : '{}' }}"
    :translations="{{ json_encode([
        'no_val' => trans('MediaManager::messages.no_val'), 
        'single_char_folder' => trans('MediaManager::messages.single_char_folder'), 
        'downloaded' => trans('MediaManager::messages.downloaded'), 
        'sep_download' => trans('MediaManager::messages.sep_download'), 
        'upload_success' => trans('MediaManager::messages.upload_success'), 
        'create_success' => trans('MediaManager::messages.create_success'), 
        'rename_success' => trans('MediaManager::messages.rename_success'), 
        'move_success' => trans('MediaManager::messages.move_success'), 
        'delete_success' => trans('MediaManager::messages.delete_success'), 
        'copy_success' => trans('MediaManager::messages.copy_success'), 
        'save_success' => trans('MediaManager::messages.save_success'), 
        'error_altered_fwli' => trans('MediaManager::messages.error_altered_fwli'), 
        'stand_by' => trans('MediaManager::messages.stand_by'), 
        'stream_exit_error' => trans('MediaManager::messages.stream_exit_error')
    ]) }}"
    :routes="{{ json_encode([
        'files' => route('media.files'), 
        'dirs' => route('media.directories'), 
        'lock' => route('media.lock_file'), 
        'visibility' => route('media.change_vis'), 
    ]) }}"
    :user-id="{{ auth()->user()->id ?? 0 }}"
    :upload-panel-img-list="{{ $patterns }}">

    <div class="">

        {{-- notif-audio --}}
        <audio ref="alert-audio" src="{{ asset('assets/vendor/MediaManager/audio/alert.mp3') }}"></audio>
        <audio ref="success-audio" src="{{ asset('assets/vendor/MediaManager/audio/success.mp3') }}"></audio>

        {{-- top toolbar --}}
        <transition name="list" mode="out-in">
            <nav class="media-manager__toolbar level" v-show="toolBar">

                {{-- left toolbar --}}
                <div class="level-left">
                    {{-- first --}}
                    <div class="level-item">
                        <div class="field has-addons">
                            {{-- upload --}}
                            <div class="control">
                                <button class="button"
                                    v-show="!isBulkSelecting()"
                                    ref="upload"
                                    :disabled="isLoading"
                                    @click="toggleUploadPanel()"
                                    v-tippy
                                    title="u">
                                    <span class="icon"><icon name="shopping-basket"></icon></span>
                                    <span>{{ trans('MediaManager::messages.upload') }}</span>
                                </button>
                            </div>

                            {{-- new folder --}}
                            <div class="control" v-if="!restrictModeIsOn()">
                                <button class="button"
                                    :disabled="isLoading"
                                    @click="toggleModal('new_folder_modal')">
                                    <span class="icon"><icon name="folder"></icon></span>
                                    <span>{{ trans('MediaManager::messages.add_folder') }}</span>
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
                                    <span>{{ trans('MediaManager::messages.move') }}</span>
                                </button>
                            </div>

                            {{-- rename --}}
                            <div class="control" v-if="!isBulkSelecting()">
                                <button class="button is-link"
                                    :disabled="item_ops() || isLoading"
                                    @click="renameItem()">
                                    <span class="icon"><icon name="terminal"></icon></span>
                                    <span>{{ trans('MediaManager::messages.rename') }}</span>
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
                                    <span>{{ trans('MediaManager::messages.editor') }}</span>
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
                                    <span>{{ trans('MediaManager::messages.delete') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- last --}}
                    <div class="level-item">
                        <div class="field has-addons">
                            {{-- refresh --}}
                            <div class="control" v-show="!isBulkSelecting()">
                                <v-touch class="button is-primary"
                                    ref="refresh"
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
                                        <span>{{ trans('MediaManager::messages.select_non') }}</span>
                                    </template>
                                    <template v-else>
                                        <span class="icon"><icon name="plus" scale="0.8"></icon></span>
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
                                    :disabled="searchItemsCount == 0 || !allItemsCount || isLoading"
                                    v-tippy
                                    title="b">
                                    <span class="icon"><icon name="puzzle-piece"></icon></span>
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
                                            title="{{ trans('MediaManager::messages.filter_by', ['attr' => trans('MediaManager::messages.audio')]) }}"
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
                                    <p class="control has-icons-left">
                                        <input class="input"
                                            :disabled="isLoading"
                                            type="text"
                                            v-model="searchFor"
                                            data-search
                                            placeholder="{{ trans('MediaManager::messages.find') }}">
                                        <span class="icon is-left">
                                            <icon name="search"></icon>
                                        </span>
                                    </p>
                                    <p class="control">
                                        <button class="button is-black" :disabled="!searchFor"
                                            v-tippy
                                            title="{{ trans('MediaManager::messages.clear', ['attr' => trans('MediaManager::messages.search')]) }}"
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
        <transition-group name="list" mode="out-in" tag="section">
            <div key="1" v-show="toggleUploadArea" class="media-manager__dz">
                <form id="new-upload" action="{{ route('media.upload') }}" :style="uploadPanelImg">
                    {{ csrf_field() }}
                    <input type="hidden" name="upload_path" :value="files.path">

                    {{-- text --}}
                    <div class="dz-message title is-4">{!! trans('MediaManager::messages.upload_text') !!}</div>

                    {{-- randomNames --}}
                    <div class="form-switcher"
                        title="{{ trans('MediaManager::messages.use_random_names') }}"
                        v-tippy="{arrow: true, position: 'right'}">
                        <input type="checkbox" name="random_names" id="random_names" v-model="randomNames">
                        <label class="switcher" for="random_names"></label>
                    </div>

                    {{-- urlToUpload --}}
                    <div class="save_link" @click="toggleModal('save_link_modal')" v-if="!restrictUpload()">
                        <span class="icon is-large"
                            title="{{ trans('MediaManager::messages.save_link') }}"
                            v-tippy="{arrow: true, position: 'left'}">
                            <icon>
                                <icon class="circle" name="circle" scale="2.5"></icon>
                                <icon class="anchor" name="link"></icon>
                            </icon>
                        </span>
                    </div>
                </form>

                <div id="uploadPreview"></div>
            </div>

            <div key="2" id="uploadProgress" class="progress" v-show="showProgress">
                <div class="progress-bar is-success progress-bar-striped active" :style="{width: progressCounter}"></div>
            </div>
        </transition-group>

        {{-- ====================================================================== --}}

        {{-- mobile breadCrumb --}}
        @if($mobile_alt_navigation)
            @include('MediaManager::partials._breadcrumb')
        @endif

        {{-- ====================================================================== --}}

        <div class="media-manager__stack">
            <section class="media-manager__stack-container">

                {{-- loadings --}}
                <section>
                    {{-- loading data from server --}}
                    <div id="loading_files" v-show="loading_files">
                        <div id="loading_files_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/world.json') }}"></div>

                        <transition name="list" mode="out-in">
                            <h3 key="1" v-if="showProgress">{{ trans('MediaManager::messages.stand_by') }}</h3>
                            <h3 key="2" v-else>{{ trans('MediaManager::messages.loading') }}</h3>
                        </transition>
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
                <v-touch class="media-manager__stack-files"
                    ref="__stack-files"
                    @swiperight="goToPrevFolder()">

                    {{-- no search --}}
                    <section>
                        <div id="no_search" v-show="no_search">
                            <div id="no_search_anim" data-json="{{ asset('assets/vendor/MediaManager/BM/ice_cream.json') }}"></div>
                            <h3>{{ trans('MediaManager::messages.nothing_found') }}</h3>
                        </div>
                    </section>

                    {{-- files --}}
                    <ul class="__files-boxs" ref="filesList">
                        <li v-for="(file, index) in orderBy(filterBy(allFiles, searchFor, 'name'), sortBy, -1)"
                            :key="index"
                            :data-file-index="index"
                            @click="setSelected(file, index, $event)">
                            <v-touch class="__file-box"
                                :class="{'bulk-selected': IsInBulkList(file), 'selected' : selectedFile == file}"
                                @swipeup="setSelected(file, index), moveItem()"
                                @swipedown="setSelected(file, index), deleteItem()"
                                @dbltap="dbltap()"
                                @hold="setSelected(file, index), imageEditor()">

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
                                    :title="linkCopied ? '{{ trans('MediaManager::messages.copied') }}' : '{{ trans('MediaManager::messages.copy_to_cp') }}'"
                                    v-tippy="{arrow: true, hideOnClick: false}"
                                    @hidden="linkCopied = false">
                                    <icon name="clone" scale="0.9"></icon>
                                </div>

                                <div class="__box-data">
                                    <div class="__box-preview">
                                        <template v-if="fileTypeIs(file, 'image')">
                                            <image-cache v-if="config.lazyLoad" :url="file.path" :db="CDBN"></image-cache>
                                            <image-intersect v-else :url="file.path"></image-intersect>
                                        </template>

                                        <span v-else class="icon is-large">
                                            <icon v-if="fileTypeIs(file, 'folder')" name="folder" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'application')" name="cogs" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'video')" name="film" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'audio')" name="music" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'pdf')" name="file-pdf-o" scale="2.6"></icon>
                                            <icon v-if="fileTypeIs(file, 'text')" name="file-text-o" scale="2.6"></icon>
                                        </span>
                                    </div>

                                    <div class="__box-info">
                                        {{-- folder --}}
                                        <template v-if="fileTypeIs(file, 'folder')">
                                            <h4>@{{ file.name }}</h4>
                                            <small>
                                                <span>@{{ file.items }} {{ trans('MediaManager::messages.items') }}</span>
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
                <transition name="slide" mode="out-in" appear>
                    <div class="media-manager__stack-sidebar is-hidden-touch" v-if="toggleInfo">
                        {{-- preview --}}
                        <div class="__sidebar-preview">
                            <transition name="slide" mode="out-in" appear>
                                {{-- no selection --}}
                                <div key="0" class="__sidebar-none-selected" v-if="!selectedFile">
                                    <span @click="reset()" class="link"><icon name="power-off" scale="3.2"></icon></span>
                                    <p>{{ trans('MediaManager::messages.nothing_selected') }}</p>
                                </div>

                                {{-- img --}}
                                <template v-else-if="selectedFileIs('image')">
                                    <img :src="selectedFilePreview"
                                        :alt="selectedFile.name"
                                        :key="selectedFile.name"
                                        v-tippy="{arrow: true, position: 'left'}"
                                        title="space"
                                        class="link image"
                                        @click="isBulkSelecting() ? false : toggleModal('preview_modal')"/>
                                </template>

                                {{-- video --}}
                                <template v-else-if="selectedFileIs('video')">
                                    <video controls
                                        preload="none"
                                        class="__sidebar-video"
                                        ref="player"
                                        :key="selectedFile.name"
                                        v-tippy="{arrow: true, position: 'left'}"
                                        title="space"
                                        :src="selectedFile.path">
                                        {{ trans('MediaManager::messages.video_support') }}
                                    </video>
                                </template>

                                {{-- audio --}}
                                <template v-else-if="selectedFileIs('audio')">
                                    <div :key="selectedFile.name">
                                        <audio controls
                                            preload="none"
                                            class="__sidebar-audio"
                                            ref="player"
                                            v-tippy="{arrow: true, position: 'left'}"
                                            title="space"
                                            :src="selectedFile.path">
                                            {{ trans('MediaManager::messages.audio_support') }}
                                        </audio>
                                        <img v-if="selectedFilePreview"
                                            :src="selectedFilePreview"
                                            :alt="selectedFile.name"
                                            class="image"/>
                                    </div>
                                </template>

                                {{-- icons --}}
                                <icon key="1" v-else-if="selectedFileIs('folder')" name="folder" scale="4"></icon>
                                <icon key="2" v-else-if="selectedFileIs('application')" name="cogs" scale="4"></icon>
                                <div key="3" v-else-if="selectedFileIs('pdf')"
                                    class="link"
                                    v-tippy="{arrow: true, position: 'left'}"
                                    title="space"
                                    @click="toggleModal('preview_modal')">
                                    <icon name="file-pdf-o" scale="4"></icon>
                                </div>
                                <div key="4" v-else-if="selectedFileIs('text')"
                                    class="link"
                                    v-tippy="{arrow: true, position: 'left'}"
                                    title="space"
                                    @click="toggleModal('preview_modal')">
                                    <icon name="file-text-o" scale="4"></icon>
                                </div>
                            </transition>
                        </div>

                        {{-- info --}}
                        <div v-if="allItemsCount"
                            class="__sidebar-info"
                            :style="selectedFile ? 'background-color: white' : ''">

                            <transition name="list" mode="out-in" appear>
                                <div :key="selectedFile.name" v-if="selectedFile">
                                    <p>{{ trans('MediaManager::messages.name') }}: <span>@{{ selectedFile.name }}</span></p>
                                    <p>{{ trans('MediaManager::messages.type') }}: <span>@{{ selectedFile.type }}</span></p>
                                    <p>{{ trans('MediaManager::messages.size') }}: <span>@{{ getFileSize(selectedFile.size) }}</span></p>

                                    {{-- folder --}}
                                    <template v-if="selectedFileIs('folder')">
                                        <p>{{ trans('MediaManager::messages.items') }}: <span>@{{ selectedFile.items }}</span></p>

                                        <div class="__sidebar-zip" v-show="!isBulkSelecting()">
                                            <span>{{ trans('MediaManager::messages.download_folder') }}:</span>
                                            <form action="{{ route('media.folder_download') }}" method="post" @submit.prevent="ZipDownload($event)">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="folders" :value="folders.length ? '/' + folders.join('/') : null">
                                                <input type="hidden" name="name" :value="selectedFile.name">
                                                <button type="submit" class="btn-plain zip" :disabled="selectedFile.items == 0">
                                                    <span class="icon"><icon name="archive" scale="1.2"></icon></span>
                                                </button>
                                            </form>
                                        </div>
                                    </template>

                                    {{-- file --}}
                                    <template v-else>
                                        <p v-if="selectedFileIs('image') && dimensions.length">
                                            {{ trans('MediaManager::messages.dimension') }}: <span>@{{ selectedFileDimensions }}</span>
                                        </p>
                                        <p>{{ trans('MediaManager::messages.visibility') }}: <span>@{{ selectedFile.visibility }}</span></p>
                                        <p>
                                            {{ trans('MediaManager::messages.preview') }}:
                                            <a :href="selectedFile.path" target="_blank" rel="noreferrer noopener">{{ trans('MediaManager::messages.public_url') }}</a>
                                        </p>
                                        <div class="__sidebar-zip">
                                            <span>{{ trans('MediaManager::messages.download_file') }}:</span>
                                            {{-- normal --}}
                                            <button class="btn-plain" @click.prevent="saveFile(selectedFile)">
                                                <span class="icon"><icon name="download" scale="1.2"></icon></span>
                                            </button>
                                            {{-- zip --}}
                                            <form action="{{ route('media.files_download') }}" method="post" @submit.prevent="ZipDownload($event)" v-show="isBulkSelecting()">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="list" :value="JSON.stringify(bulkList)">
                                                <input type="hidden" name="name" :value="folders.length ? folders[folders.length - 1] : 'media_manager'">
                                                <button type="submit" class="btn-plain zip" :disabled="hasFolder()">
                                                    <span class="icon"><icon name="archive" scale="1.2"></icon></span>
                                                </button>
                                            </form>
                                        </div>
                                    </template>

                                    <p>{{ trans('MediaManager::messages.last_modified') }}: <span>@{{ selectedFile.last_modified_formated }}</span></p>
                                </div>

                                {{-- keep the counts at bottom --}}
                                <div v-else></div>
                            </transition>

                            {{-- items count --}}
                            <transition-group tag="div" name="counter" mode="out-in" class="__sidebar-count">
                                {{-- all --}}
                                <div key="1" v-if="allItemsCount">
                                    <p class="title is-1">@{{ allItemsCount }}</p>
                                    <p class="heading">{{ trans('MediaManager::messages.total') }}</p>
                                </div>

                                {{-- search --}}
                                <div key="2" v-if="searchItemsCount !== null && searchItemsCount >= 0">
                                    <p class="title is-1">@{{ searchItemsCount }}</p>
                                    <p class="heading">{{ trans('MediaManager::messages.found') }}</p>
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
                {{-- directories breadCrumb --}}
                <div class="level-left">
                    <nav class="breadcrumb {{ $mobile_alt_navigation ? 'is-hidden-touch' : '' }}" v-if="!restrictModeIsOn()">
                        <transition-group tag="ul" name="list" mode="out-in">
                            <li key="library-bc">
                                <a v-if="folders.length > 0 && !(isBulkSelecting() || isLoading)"
                                    class="p-l-0"
                                    v-tippy
                                    title="backspace"
                                    @click="goToFolder(0)">
                                    {{ trans('MediaManager::messages.library') }}
                                </a>
                                <p v-else class="p-l-0">{{ trans('MediaManager::messages.library') }}</p>
                            </li>

                            <li v-for="(folder, index) in folders" :key="index">
                                <p v-if="isLastItem(folder, folders) || isBulkSelecting() || isLoading">@{{ folder }}</p>
                                <a v-else
                                    v-tippy
                                    title="backspace"
                                    @click="folders.length > 1 ? goToFolder(index+1) : false">
                                    @{{ folder }}
                                </a>
                            </li>
                        </transition-group>
                    </nav>
                </div>

                {{-- toggle sidebar --}}
                <div class="level-right" v-show="!isLoading">
                    <div class="is-hidden-touch"
                        @click="toggleInfoPanel()"
                        v-tippy
                        title="t"
                        v-if="allItemsCount">
                        <transition :name="toggleInfo ? 'info-out' : 'info-in'" mode="out-in">
                            <div :key="toggleInfo ? 1 : 2" class="__stack-sidebar-toggle has-text-link">
                                <template v-if="toggleInfo">
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
                    <transition :name="imageSlideDirection == 'next' ? 'img-nxt' : 'img-prv'" mode="out-in">
                        <div class="modal-content" :key="selectedFile.path">
                            {{-- card v --}}
                            @include('MediaManager::cards.vertical')
                        </div>
                    </transition>
                </div>
                <button class="modal-close is-large" @click="toggleModal()"></button>
            </div>

            {{-- image_editor --}}
            <div v-if="isActiveModal('imageEditor_modal')"
                class="modal mm-animated fadeIn is-active __modal-editor">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="mm-animated fadeInDown __modal-content-wrapper">
                    <cropper route="{{ route('media.uploadCropped') }}"
                        :url="selectedFilePreview"
                        :translations="{{ json_encode([
                            'crop_reset' => trans('MediaManager::messages.crop_reset'), 
                            'clear' => trans('MediaManager::messages.clear', ['attr' => 'selection']), 
                            'crop_apply' => trans('MediaManager::messages.crop_apply'), 
                            'move' => trans('MediaManager::messages.move'), 
                            'crop' => trans('MediaManager::messages.crop'), 
                            'crop_zoom_in' => trans('MediaManager::messages.crop_zoom_in'), 
                            'crop_zoom_out' => trans('MediaManager::messages.crop_zoom_out'), 
                            'crop_rotate_left' => trans('MediaManager::messages.crop_rotate_left'), 
                            'crop_rotate_right' => trans('MediaManager::messages.crop_rotate_right'), 
                            'crop_flip_horizontal' => trans('MediaManager::messages.crop_flip_horizontal'), 
                            'crop_flip_vertical' => trans('MediaManager::messages.crop_flip_vertical'), 
                            'save_success' => trans('MediaManager::messages.save_success'), 
                        ]) }}">
                    </cropper>
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
                            <span>{{ trans('MediaManager::messages.save_link') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.uploadLink') }}" @submit.prevent="saveLinkForm($event)">
                        <section class="modal-card-body">
                            <input class="input" type="text"
                                v-model="urlToUpload"
                                placeholder="{{ trans('MediaManager::messages.add_url') }}"
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
                                {{ trans('MediaManager::messages.upload') }}
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
                            <span>{{ trans('MediaManager::messages.add_new_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.new_folder') }}" @submit.prevent="NewFolderForm($event)">
                        <section class="modal-card-body">
                            <input class="input" type="text"
                                v-model="newFolderName"
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
                :class="{'is-active': isActiveModal('rename_file_modal')}">
                <div class="modal-background link" @click="toggleModal()"></div>
                <div class="modal-card mm-animated fadeInDown">
                    <header class="modal-card-head is-warning">
                        <p class="modal-card-title">
                            <span>{{ trans('MediaManager::messages.rename_file_folder') }}</span>
                        </p>
                        <button type="button" class="delete" @click="toggleModal()"></button>
                    </header>

                    <form action="{{ route('media.rename_file') }}" @submit.prevent="RenameFileForm($event)">
                        <section class="modal-card-body">
                            <h3 class="title">{{ trans('MediaManager::messages.new_file_folder') }}</h3>
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
                                {{ trans('MediaManager::messages.rename') }}
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
                            <transition :name="useCopy ? 'info-in' : 'info-out'" mode="out-in">
                                <span class="icon" :key="useCopy ? 1 : 2">
                                    <icon :name="useCopy ? 'clone' : 'share'"></icon>
                                </span>
                            </transition>

                            <transition name="list" mode="out-in">
                                <span key="1" v-if="useCopy">{{ trans('MediaManager::messages.copy_file_folder') }}</span>
                                <span key="2" v-else>{{ trans('MediaManager::messages.move_file_folder') }}</span>
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
                                            <option v-for="(dir, index) in directories"
                                                v-if="filterDirList(dir)"
                                                :key="index" :value="dir">
                                                @{{ dir }}
                                            </option>
                                        </select>
                                    </span>
                                    <span class="icon is-left">
                                        <icon name="search"></icon>
                                    </span>
                                </div>
                            </div>

                            {{-- btns --}}
                            <div class="field p-t-10">
                                <div class="control">
                                    <div class="level">
                                        <div class="level-right">
                                            <div class="level-item">
                                                <div class="form-switcher">
                                                    <input type="checkbox" name="use_copy" id="use_copy" v-model="useCopy">
                                                    <label class="switcher" for="use_copy"></label>
                                                </div>
                                            </div>
                                            <div class="level-item">
                                                <label class="label" for="use_copy">{{ trans('MediaManager::messages.copy_files') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <br>
                            @include('MediaManager::partials._modal-files-info')
                        </section>
                        <footer class="modal-card-foot">
                            <button type="reset" class="button" @click="toggleModal()">
                                {{ trans('MediaManager::messages.cancel') }}
                            </button>
                            <button type="submit"
                                class="button is-warning"
                                :disabled="isLoading || !moveToPath"
                                :class="{'is-loading': isLoading}"
                                v-html="useCopy
                                    ? '{{ trans('MediaManager::messages.copy') }}'
                                    : '{{ trans('MediaManager::messages.move') }}'">
                            </button>
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
                            @include('MediaManager::partials._modal-files-info')

                            {{-- deleting folder warning --}}
                            <h5 v-show="folderWarning" class="__modal-folder-warning">
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

{{-- styles --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/MediaManager/style.css') }}"/>
@endpush

{{-- scripts --}}
@push('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/camanjs/4.1.2/caman.full.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jsmediatags/3.9.0/jsmediatags.min.js"></script>
    <script src="{{ asset('assets/vendor/MediaManager/manager.js') }}"></script>
@endpush
