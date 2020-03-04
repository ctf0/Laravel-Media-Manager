<v-touch class="card"
    :class="{'pdf-prev': selectedFileIs('pdf') || selectedFileIs('text')}"
    @swiperight="cardSwipGesture"
    @swipeleft="cardSwipGesture"
    @swipeup="cardSwipGesture"
    @swipedown="cardSwipGesture">

    <div class="card-image">
        {{-- pdf / text --}}
        <object v-if="selectedFileIs('pdf') || selectedFileIs('text')"
                :data="selectedFile.path"
                :type="selectedFile.type">
            <p v-if="selectedFileIs('pdf')">{{ trans('MediaManager::messages.pdf') }}</p>
        </object>

        {{-- video --}}
        <div v-else-if="selectedFileIs('video')">
            <video controls
                playsinline
                preload="metadata"
                data-player
                :src="selectedFile.path">
                {{ trans('MediaManager::messages.video_support') }}
            </video>
        </div>

        {{-- audio --}}
        <div v-else-if="selectedFileIs('audio')" class="audio-prev">
            <template>
                <img v-if="audioFileMeta && audioFileMeta.cover"
                    :src="audioFileMeta.cover"
                    :alt="selectedFile.name"
                    class="image"/>

                <div v-else class="audio-icon">
                    <icon class="svg-prev-icon" name="music" scale="8"></icon>
                </div>
            </template>

            <audio controls
                class="is-hidden"
                preload="metadata"
                data-player
                :src="selectedFile.path">
                {{ trans('MediaManager::messages.audio.support') }}
            </audio>
        </div>

        {{-- image --}}
        <image-preview v-else
            :show-ops="true"
            :trans="trans"
            :ops_btn_disable="ops_btn_disable"
            :in-movable-list="inMovableList"
            :rename-item="renameItem"
            :delete-item="deleteItem"
            :image-editor-card="imageEditorCard"
            :add-to-movable-list="addToMovableList">
            <a :href="selectedFile.path"
                rel="noreferrer noopener"
                target="_blank"
                class="image">
                <img :src="selectedFile.path" :alt="selectedFile.name">
            </a>
        </image-preview>
    </div>

    <div class="card-content">
        <p class="card-details">
            {{-- dim --}}
            <span class="tag" v-if="(selectedFileIs('image') || selectedFileIs('video')) && dimensions.length">
                @{{ selectedFileDimensions }}
            </span>

            {{-- size --}}
            <span class="tag" v-if="selectedFile.size > 0">@{{ getFileSize(selectedFile.size) }}</span>
        </p>

        {{-- name --}}
        <p class="title is-marginless">
            <span class="link"
                @click.stop="copyLink(selectedFile.path)"
                :title="linkCopied ? trans('copied') : trans('to_cp')"
                v-tippy="{arrow: true, hideOnClick: false, followCursor: true}"
                @hidden="linkCopied = false">
                @{{ selectedFile.name }}
            </span>

            {{-- open url --}}
            <a :href="selectedFile.path"
                v-if="selectedFileIs('pdf') || selectedFileIs('text')"
                rel="noreferrer noopener"
                target="_blank"
                title="{{ trans('MediaManager::messages.public_url') }}"
                v-tippy>
                <icon name="search" scale="1.1"></icon>
            </a>
        </p>

        <p class="subtitle is-6 m-t-5">
            {{-- date --}}
            <span>@{{ selectedFile.last_modified_formated }}</span>
        </p>

        {{-- ops --}}
        <div class="level is-mobile">
            <div class="level-left">
                {{-- lock / unlock --}}
                <div class="level-item">
                    <span v-if="$refs.lock"
                        class="icon is-large link"
                        :class="IsLocked(selectedFile) ? 'is-danger' : 'is-success'"
                        :title="IsLocked(selectedFile) ? '{{ trans('MediaManager::messages.unlock') }}' : '{{ trans('MediaManager::messages.lock') }}'"
                        v-tippy="{arrow: true, hideOnClick: false}"
                        @click.stop="$refs.lock.click()">
                        <icon :name="IsLocked(selectedFile) ? 'lock' : 'unlock'" scale="1.1"></icon>
                    </span>
                </div>

                {{-- visibility --}}
                <div class="level-item">
                    <span v-if="$refs.visibility"
                        class="icon is-large link"
                        :class="IsVisible(selectedFile) ? 'is-success' : 'is-danger'"
                        title="{{ trans('MediaManager::messages.visibility.set') }}"
                        v-tippy
                        @click.stop="$refs.visibility.click()">
                        <icon :name="IsVisible(selectedFile) ? 'eye' : 'eye-slash'" scale="1.1"></icon>
                    </span>
                </div>
            </div>

            <div class="level-right">
                {{-- download --}}
                <div class="level-item">
                    <span class="icon is-large link is-black"
                        title="{{ trans('MediaManager::messages.download.file') }}"
                        v-tippy
                        @click.prevent="saveFile(selectedFile)">
                        <icon name="download" scale="1.1"></icon>
                    </span>
                </div>
            </div>
        </div>
    </div>
</v-touch>
