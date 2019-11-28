<transition-group tag="ul"
    class="__stack-breadcrumb-mobile is-hidden-desktop"
    ref="bc"
    v-if="navigationDirs.length > 0"
    name="mm-list">
    <li id="library-bc" key="library-bc">
        <a v-if="navigationDirs.length > 0 && !(isBulkSelecting() || isLoading)"
            v-tippy
            title="{{ trans('MediaManager::messages.backspace') }}"
            @click="goToFolder(0)">
            {{ trans('MediaManager::messages.library') }}
        </a>
        <p v-else>{{ trans('MediaManager::messages.library') }}</p>
    </li>

    <li v-for="(folder, index) in navigationDirs" :id="folder + '-bc'" :key="`${index}_${folder}`">
        <p v-if="isLastItemByIndex(index, navigationDirs) || isBulkSelecting() || isLoading">@{{ folder }}</p>
        <a v-else
            v-tippy
            title="{{ trans('MediaManager::messages.backspace') }}"
            @click="navigationDirs.length > 1 ? goToFolder(index+1) : false">
            @{{ folder }}
        </a>
    </li>
</transition-group>
