<transition-group tag="ul"
    class="__stack-breadcrumb-mobile is-hidden-desktop"
    ref="bc" v-show="folders.length > 0"
    name="list" mode="out-in">
    <li id="library-bc" key="library-bc">
        <a v-if="folders.length > 0 && !(isBulkSelecting() || isLoading)"
            v-tippy title="backspace"
            @click="goToFolder(0)">
            {{ trans('MediaManager::messages.library') }}
        </a>
        <p v-else>{{ trans('MediaManager::messages.library') }}</p>
    </li>

    <li v-for="(folder,index) in folders" :id="folder + '-bc'" :key="folder + '-bc'">
        <p v-if="isLastItem(folder, folders) || isBulkSelecting() || isLoading">@{{ folder }}</p>
        <a v-else v-tippy title="backspace"
            @click="folders.length > 1 ? goToFolder(index+1) : false">
            @{{ folder }}
        </a>
    </li>
</transition-group>
