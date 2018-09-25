{{-- multi --}}
<template v-if="bulkItemsFilter.length">
    <template v-if="bulkItemsFilter.length <= 8">
        <div class="media" v-for="one in bulkItemsFilter">
            <figure class="media-left">
                <span class="icon has-text-link">
                    <icon v-if="fileTypeIs(one, 'folder')" name="folder" scale="1.2"></icon>
                    <icon v-else-if="fileTypeIs(one, 'application')" name="cogs" scale="1.2"></icon>
                    <icon v-else-if="fileTypeIs(one, 'compressed')" name="file-archive-o" scale="1.2"></icon>
                    <icon v-else-if="fileTypeIs(one, 'image')" name="image" scale="1.2"></icon>
                    <icon v-else-if="fileTypeIs(one, 'video')" name="film" scale="1.2"></icon>
                    <icon v-else-if="fileTypeIs(one, 'audio')" name="music" scale="1.2"></icon>
                    <icon v-else-if="fileTypeIs(one, 'pdf')" name="file-pdf-o" scale="1.2"></icon>
                    <icon v-else-if="fileTypeIs(one, 'text')" name="file-text-o" scale="1.2"></icon>
                </span>
            </figure>
            <div class="media-content">
                <p class="title is-4">
                    <strong class="has-text-link">@{{ one.name }}</strong>
                    <small v-if="one.items" class="has-text-link">"@{{ one.items }} {{ trans('MediaManager::messages.items') }}"</small>
                </p>
                <p class="subtitle is-5 has-text-danger">@{{ getFileSize(one.size) }}</p>
            </div>
        </div>
    </template>

    {{-- more than 8 items --}}
    <template v-else>
        <div class="media">
            <figure class="media-left">
                <span class="icon has-text-link"><icon name="archive" scale="1.5"></icon></span>
            </figure>
            <div class="media-content">
                <p class="title is-4">
                    <strong>"@{{ bulkItemsFilter.length }}"</strong>
                    <small class="has-text-link">{{ trans('MediaManager::messages.too_many_files') }}</small>
                </p>
            </div>
        </div>
    </template>

    {{-- total size --}}
    <p v-if="bulkItemsFilterSize" class="__modal-delete-total">
        <span class="title">@{{ bulkItemsFilterSize }}</span>
        <span class="heading">{{ trans('MediaManager::messages.total') }}</span>
    </p>
</template>

{{-- single --}}
<template v-else>
    <template v-if="selectedFile">
        <div class="media">
            <figure class="media-left">
                <span class="icon has-text-link">
                    <icon v-if="selectedFileIs('folder')" name="folder" scale="1.8"></icon>
                    <icon v-else-if="selectedFileIs('application')" name="cogs" scale="1.8"></icon>
                    <icon v-else-if="selectedFileIs('compressed')" name="file-archive-o" scale="1.8"></icon>
                    <icon v-else-if="selectedFileIs('image')" name="image" scale="1.8"></icon>
                    <icon v-else-if="selectedFileIs('video')" name="film" scale="1.8"></icon>
                    <icon v-else-if="selectedFileIs('audio')" name="music" scale="1.8"></icon>
                    <icon v-else-if="selectedFileIs('pdf')" name="file-pdf-o" scale="1.8"></icon>
                    <icon v-else-if="selectedFileIs('text')" name="file-text-o" scale="1.8"></icon>
                </span>
            </figure>
            <div class="media-content">
                <p class="title is-4">
                    <strong class="has-text-link">@{{ selectedFile.name }}</strong>
                    <small v-if="selectedFile.items" class="has-text-link">
                        "@{{ selectedFile.items }} {{ trans('MediaManager::messages.items') }}"
                    </small>
                </p>
                <p class="subtitle is-5 has-text-danger">@{{ getFileSize(selectedFile.size) }}</p>
            </div>
        </div>
    </template>
</template>
