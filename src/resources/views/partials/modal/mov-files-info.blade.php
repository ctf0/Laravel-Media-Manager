{{-- multi --}}
<template v-if="movableItemsFilter.length">
    <ul>
        <li class="media link"
            v-for="(one, i) in movableItemsFilter"
            :key="one.path"
            v-tippy="{arrow: true, placement: 'bottom'}"
            title="{{ trans('MediaManager::messages.remove') }}"
            @click.stop="removeFromMovableList(i)">
            <figure class="media-left">
                <span class="icon has-text-link">
                    <icon-types :file="one" :file-type-is="fileTypeIs"/>
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
        </li>
    </ul>

    {{-- total size --}}
    <p v-if="movableItemsFilterSize" class="__modal-delete-total">
        <span class="title">@{{ movableItemsFilterSize }}</span>
        <span class="heading">{{ trans('MediaManager::messages.total') }}</span>
    </p>
</template>
