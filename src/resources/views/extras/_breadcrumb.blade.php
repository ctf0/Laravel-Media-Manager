<ol class="__stack-breadcrumb-mobile is-hidden-desktop" ref="bc">
    <li id="home-bc" v-if="!checkForRestrictedPath()">
        <a v-if="folders.length > 0 && !(isBulkSelecting() || isLoading)"
            v-tippy title="backspace"
            @click="goToFolder(0)">
            {{ trans('MediaManager::messages.library') }}
        </a>
        <p v-else>{{ trans('MediaManager::messages.library') }}</p>
    </li>

    <template v-for="(folder,index) in folders">
        <li :id="folder + '-bc'" @click="folders.length > 1 ? goToFolder(index+1) : false">
            <p v-if="isLastItem(folder, folders) || isBulkSelecting() || isLoading">@{{ folder }}</p>
            <a v-else v-tippy title="backspace">@{{ folder }}</a>
        </li>
    </template>
</ol>
