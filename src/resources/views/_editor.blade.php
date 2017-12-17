<editor-media-manager inline-template>
    <div>
        <template v-if="showModal">
            @include('MediaManager::_modal')
        </template>

        <button class="__Inmodal-editor" @click="showModal = true"></button>
    </div>
</editor-media-manager>
