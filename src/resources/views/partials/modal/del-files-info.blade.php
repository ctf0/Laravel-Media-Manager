{{-- multi --}}
<template v-if="bulkItemsFilter.length">
    {{-- less than 8 items --}}
    <template v-if="bulkItemsFilter.length <= 8">
        <div class="media" v-for="one in bulkItemsFilter" :key="one.path">
            <figure class="media-left">
                <span class="icon has-text-link">
                    <icon-types :file="selectedFile" :file-type-is="fileTypeIs"/>
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
                    <icon-types :file="selectedFile" :file-type-is="fileTypeIs" :scale="1.8"/>
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
