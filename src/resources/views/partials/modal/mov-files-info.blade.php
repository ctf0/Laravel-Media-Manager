{{-- multi --}}
<template v-if="movableItemsFilter.length">
    <transition-group tag="div" name="mm-slide">
        <div class="media link"
            v-for="(one, i) in movableItemsFilter"
            :key="one.path"
            v-tippy="{arrow: true, position: 'bottom'}"
            title="{{ trans('MediaManager::messages.remove') }}"
            @click="removeFromMovableList(i)">
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
                    <strong class="has-text-link">@{{ one.storage_path }}</strong>
                    <small v-if="one.items" class="has-text-link">"@{{ one.items }} {{ trans('MediaManager::messages.items') }}"</small>
                </p>
                <p class="subtitle is-5 has-text-danger">@{{ getFileSize(one.size) }}</p>
            </div>
            <figure class="media-right">
                <span class="delete"></span>
            </figure>
        </div>
    </transition-group>

    {{-- total size --}}
    <p v-if="movableItemsFilterSize" class="__modal-delete-total">
        <span class="title">@{{ movableItemsFilterSize }}</span>
        <span class="heading">{{ trans('MediaManager::messages.total') }}</span>
    </p>
</template>
