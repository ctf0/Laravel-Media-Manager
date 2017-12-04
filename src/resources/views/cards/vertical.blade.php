<div class="card image-preview-content">
    <div class="card-image">
        <a :href="selectedFile.path" target="_blank" class="image"><img :src="selectedFile.path"></a>
    </div>

    <div class="card-content">
        <div class="level">
            {{-- lock / name / date --}}
            <div class="level-left">
                <div class="level-item">
                    <div class="media">
                        <div class="media-left link">
                            <span class="icon is-large"
                                :class="IsInLockedList(selectedFile) ? 'is-danger' : 'is-success'"
                                :title="IsInLockedList(selectedFile) ? '{{ trans('MediaManager::messages.unlock') }}': '{{ trans('MediaManager::messages.lock') }}'"
                                v-tippy="{arrow: true, hideOnClick: false}"
                                @click="toggleLock(selectedFile)">
                                <span class="icon is-small">
                                    <i class="fa fa-lg" :class="IsInLockedList(selectedFile) ? 'fa-unlock' : 'fa-lock'"></i>
                                </span>
                            </span>
                        </div>
                        <div class="media-content">
                            <p class="title">@{{ selectedFile.name }}</p>
                            <p class="heading">@{{ selectedFile.last_modified_formated }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- download / size --}}
            <div class="level-right">
                <div class="level-item has-text-centered">
                    <div>
                        <button class="btn-plain" @click.prevent="saveFile(selectedFile)"
                            v-tippy="{arrow: true}" title="{{ trans('MediaManager::messages.download_file') }}">
                            <span class="icon has-text-black"><i class="fa fa-download fa-3x"></i></span>
                        </button>
                        <p>@{{ getFileSize(selectedFile.size) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="card-footer">
        <div class="card-footer-item">
            <button class="button btn-plain is-fullwidth"
                v-multi-ref="'move'"
                :disabled="mv_dl() || !checkForFolders"
                @click="moveItem()">
                <span class="icon is-small"><i class="fa fa-share"></i></span>
                <span>{{ trans('MediaManager::messages.move') }}</span>
            </button>
        </div>

        <div class="card-footer-item">
            <button class="button btn-plain is-fullwidth"
                :disabled="!selectedFile || IsInLockedList(selectedFile)"
                v-if="!isBulkSelecting()"
                @click="renameItem()">
                <span class="icon is-small"><i class="fa fa-i-cursor"></i></span>
                <span>{{ trans('MediaManager::messages.rename') }}</span>
            </button>
        </div>

        <div class="card-footer-item">
            <button class="button btn-plain is-fullwidth"
                v-multi-ref="'delete'"
                :disabled="mv_dl()"
                @click="deleteItem()">
                <span class="icon is-small"><i class="fa fa-trash"></i></span>
                <span>{{ trans('MediaManager::messages.delete') }}</span>
            </button>
        </div>

      </footer>
</div>
