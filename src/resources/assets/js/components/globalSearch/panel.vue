<template>
    <div v-if="showPanel"
         id="gsearch-panel"
         class="modal mm-animated fadeIn is-active">
        <div class="modal-background"/>
        <div class="modal-content">
            <div ref="search-input"
                 :class="{'move': firstRun}"
                 class="search-input">
                <section>
                    <div class="input-wrapper">
                        <input ref="search"
                               v-model="search"
                               v-autowidth
                               :placeholder="trans('find')"
                               autofocus>
                    </div>
                    <transition name="mm-info-in">
                        <div v-show="listCount"
                             class="count">
                            <p class="title is-marginless is-2">
                                {{ listCount }}
                            </p>
                            <p class="heading is-marginless">
                                {{ trans('found') }}
                            </p>
                        </div>
                    </transition>
                </section>
            </div>

            <transition-group name="mm-gs"
                              tag="ul"
                              class="columns is-multiline is-marginless">
                <li v-for="(item, i) in filterdFilesList"
                    :key="`${i}-${item.name}`"
                    class="column is-2">
                    <div class="card">
                        <div class="card-image">
                            <a v-if="fileTypeIs(item, 'image')"
                               :href="item.path"
                               target="_blank"
                               class="image">
                                <image-intersect :file="item"
                                                 :browser-support="browserSupport"
                                                 root-el="#gsearch-panel"/>
                            </a>

                            <div v-else
                                 class="glbl_search_panel">
                                <icon-types :file="item"
                                            :file-type-is="fileTypeIs"
                                            :scale="5"
                                            classes="svg-prev-icon"
                                            :except="['image']"/>
                            </div>
                        </div>
                        <div class="card-content">
                            <p v-tippy="{arrow: true, hideOnClick: false, followCursor: true}"
                               :title="linkCopied ? trans('copied') : trans('to_cp')"
                               class="title is-marginless link"
                               @click.stop="copyLink(item.path)"
                               @hidden="linkCopied = false">
                                {{ item.name }}
                            </p>
                            <br>

                            <p class="subtitle is-marginless link"
                               @click.stop="goToFolder(item.dir_path, item.name)">
                                <span class="icon"><icon name="folder"/></span>
                                <span v-tippy="{arrow: true}"
                                      :title="trans('go_to_folder')">{{ item.dir_path }}</span>
                            </p>
                            <time>
                                <span class="icon"><icon name="regular/clock"/></span>
                                <span>{{ item.last_modified_formated }}</span>
                            </time>
                            <p class="subtitle is-marginless link"
                               @click.stop="deleteItem(item, i)">
                                <span class="icon"><icon name="times"/></span>
                                <span>{{ trans('delete') }}</span>
                            </p>
                            <p class="subtitle is-marginless link"
                               @click.stop="addToMovableList(item)">
                                <span class="icon"><icon name="shopping-cart"/></span>
                                <span>{{ inMovableList(item) ? trans('added') : trans('add_to_list') }}</span>
                            </p>
                        </div>
                    </div>
                </li>

                <!-- nothing found -->
                <li v-if="noData"
                    key="noData"
                    class="column no-data">
                    <p class="title">
                        {{ trans('nothing_found') }} !!
                    </p>
                </li>
            </transition-group>
        </div>
        <button class="modal-close is-large"
                @click.stop="closePanel()"/>
    </div>
</template>

<style scoped lang="scss" src="../../../sass/modules/global-search.scss"></style>

<script>
import debounce          from 'lodash/debounce'
import VueInputAutowidth from 'vue-input-autowidth'
import panels            from '../../mixins/panels'

export default {
    components: {
        imageIntersect: require('./image.vue').default
    },
    directives: {
        VueInputAutowidth
    },
    mixins : [panels],
    props  : [
        'trans',
        'fileTypeIs',
        'noScroll',
        'browserSupport',
        'addToMovableList',
        'inMovableList'
    ],
    data() {
        return {
            filesIndex       : [],
            filterdFilesList : [],
            search           : '',
            noData           : false,
            linkCopied       : false,
            firstRun         : false,
            showPanel        : false
        }
    },
    computed: {
        fuseLib() {
            return new Fuse(this.filesIndex, {
                keys      : ['name'],
                threshold : 0.4
            })
        },
        listCount() {
            return this.filterdFilesList.length
        }
    },
    methods: {
        eventsListener() {
            EventHub.listen('global-search-index', (data) => {
                this.filesIndex = data
            })

            EventHub.listen('toggle-global-search', (data) => {
                this.showPanel = data
            })

            EventHub.listen('clear-global-search', () => {
                this.search = ''
            })

            EventHub.listen('global-search-deleted', (path) => {
                let list = this.filterdFilesList

                return list.some((e, i) => {
                    if (e.path == path) {
                        list.splice(i, 1)
                    }
                })
            })
        },
        copyLink(path) {
            this.linkCopied = true
            this.$copyText(path)
        },
        deleteItem(item, i) {
            EventHub.fire('global-search-delete-item', item)
        },
        closePanel() {
            this.search    = ''
            this.showPanel = false
            EventHub.fire('toggle-global-search', false)
        },
        goToFolder(dir, name) {
            EventHub.fire('global-search-go-to-folder', {
                dir  : dir,
                name : name
            })

            this.closePanel()
        },
        getList: debounce(function() {
            let search = this.search

            if (search) {
                this.filterdFilesList = this.fuseLib.search(search)

                return this.noData = this.listCount ? false : true
            }

            this.filterdFilesList = []
        }, 500),
        ontransitionend() {
            this.noData   = this.search && !this.listCount ? true : false
            this.firstRun = true
        }
    },
    watch: {
        showPanel(val) {
            this.showPanelWatcher(val)

            if (val) {
                this.$nextTick(() => this.$refs.search.focus())
            } else {
                this.$nextTick(() => {
                    this.noData   = false
                    this.firstRun = false
                })
            }
        },
        search(val) {
            this.getList()

            if (!this.firstRun) {
                this.$nextTick(() => this.firstRun = true)
            }
        },
        firstRun(val) {
            let ref = this.$refs['search-input']

            if (!val) {
                if (ref) {
                    ref.addEventListener('transitionend', this.ontransitionend)
                }
            } else {
                ref.removeEventListener('transitionend', this.ontransitionend)
            }
        }
    }
}
</script>
