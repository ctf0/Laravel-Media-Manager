<section>
    {{-- preview_modal --}}
    <div v-if="isActiveModal('preview_modal')"
        class="modal mm-animated fadeIn is-active __modal-preview">
        <div class="modal-background link" @click="toggleModal()"></div>
        <div class="mm-animated fadeInDown __modal-content-wrapper">
            <transition :name="`mm-img-${imageSlideDirection}`"
                mode="out-in"
                appear
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
                :no-scroll="noScroll"
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
                    @include('MediaManager::partials.modal.files-info')
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

            <form action="{{ route('media.delete_file') }}"
                @submit.prevent="DeleteFileForm($event)">
                <section class="modal-card-body">
                    @include('MediaManager::partials.modal.files-info')

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
