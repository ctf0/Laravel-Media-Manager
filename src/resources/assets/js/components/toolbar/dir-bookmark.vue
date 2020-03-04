<template>
    <my-dropdown :disabled="disabled">
        <template v-slot:title>
            <span v-show="bmCount"
                  class="has-text-danger">{{ bmCount }}</span>
            <span>{{ trans('bm') }}</span>
        </template>

        <template v-slot:content>
            <!-- list -->
            <div v-for="(item, i) in bookmarks"
                 :key="item.dir"
                 class="dropdown-item">
                <div class="tags has-addons">
                    <!-- name -->
                    <span v-if="!isCurrent(item.dir)"
                          v-tippy="{arrow: true, placement: 'left'}"
                          :title="goTo(item.dir)"
                          class="tag is-link is-primary link"
                          @click.stop="goToFolder(item.dir)">
                        {{ getName(item.name) }}
                    </span>
                    <span v-else
                          class="tag is-dark disabled">{{ getName(item.name) }}</span>

                    <!-- remove -->
                    <a class="tag is-danger is-delete"
                       @click.stop="removeFromBookmarks(i)"/>
                </div>
            </div>

            <!-- ops -->
            <div class="dropdown-item btns">
                <div class="field has-addons">
                    <!-- add -->
                    <p v-if="!inBookmarks"
                       class="control">
                        <button type="button"
                                class="button is-small is-light is-primary"
                                @click.stop="addToBookmarks()">
                            {{ trans('bm_add_to_list') }}
                        </button>
                    </p>

                    <!-- reset -->
                    <p class="control">
                        <button type="button"
                                :disabled="!bmCount"
                                class="button is-danger is-small is-light"
                                @click.stop="clearBookmarks()">
                            {{ trans('reset') }}
                        </button>
                    </p>
                </div>
            </div>
        </template>
    </my-dropdown>
</template>

<style lang="scss" scoped>
    .control {
        flex: 1;

        button {
            width: 100%;
        }
    }

    .btns {
        margin-top: 0.5rem;
    }

    .tags {
        flex-wrap: nowrap;
    }

</style>

<script>
import uniq from 'lodash/uniq'

export default {
    props: [
        'dirBookmarks',
        'disabled',
        'path',
        'trans'
    ],
    data() {
        return {
            bookmarks: this.dirBookmarks
        }
    },
    computed: {
        currentPath() {
            return this.path || '/'
        },
        inBookmarks() {
            return this.bookmarks.some((e) => e.dir == this.currentPath)
        },
        bmCount() {
            return this.bookmarks.length
        }
    },
    methods: {
        // goto
        goToFolder(dir) {
            EventHub.fire('global-search-go-to-folder', {
                dir: dir,
                name: null
            })
        },
        goTo(dir) {
            return `${this.trans('open')} "${dir}"`
        },
        getName(str) {
            let len = str.length
            let max = 25

            return len > max
                ? str.substring(0, max) + '...'
                : str
        },
        isCurrent(dir) {
            return dir == this.currentPath
        },

        // bms
        addToBookmarks() {
            if (!this.inBookmarks) {
                let list = [].concat(
                    this.bookmarks,
                    [{
                        dir: this.currentPath,
                        name: this.currentPath
                    }]
                )

                this.bookmarks = uniq(list)
            }
        },
        removeFromBookmarks(i) {
            return this.bookmarks.splice(i, 1)
        },
        clearBookmarks() {
            return this.bookmarks = []
        }
    },
    watch: {
        bookmarks(val) {
            EventHub.fire('dir-bookmarks-update', val)
        }
    }
}
</script>
