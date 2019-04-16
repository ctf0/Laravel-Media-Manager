<template>
    <div v-if="showPanel" id="gsearch-panel" class="modal mm-animated fadeIn is-active">
        <div class="modal-background"/>
        <div class="modal-content">
            <div ref="search-input" :class="{'move': firstRun}" class="search-input">
                <section>
                    <div class="input-wrapper">
                        <input v-autowidth
                               ref="search"
                               v-model="search"
                               :placeholder="trans('find')"
                               autofocus>
                    </div>
                    <transition name="mm-info-in">
                        <div v-show="listCount" class="count">
                            <p class="title is-marginless is-2">{{ listCount }}</p>
                            <p class="heading is-marginless">{{ trans('found') }}</p>
                        </div>
                    </transition>
                </section>
            </div>

            <transition-group name="mm-gs" tag="ul" class="columns is-multiline" mode="out-in">
                <li v-list-rendered="[i, filterdList, orch]"
                    v-for="(item, i) in filterdList"
                    :key="`${i}-${item.name}`"
                    class="column is-2">

                    <div class="card">
                        <div class="card-image">
                            <a v-if="fileTypeIs(item, 'image')" :href="item.path" target="_blank" class="image">
                                <image-intersect :file="item" :browser-support="browserSupport" root-el="#gsearch-panel"/>
                            </a>

                            <div v-else class="glbl_search_panel">
                                <icon v-if="fileTypeIs(item, 'folder')" class="svg-prev-icon" name="folder" scale="5.0"/>
                                <icon v-else-if="fileTypeIs(item, 'application')" class="svg-prev-icon" name="cogs" scale="5.0"/>
                                <icon v-else-if="fileTypeIs(item, 'video')" class="svg-prev-icon" name="film" scale="5.0"/>
                                <icon v-else-if="fileTypeIs(item, 'audio')" class="svg-prev-icon" name="music" scale="5.0"/>
                                <icon v-else-if="fileTypeIs(item, 'pdf')" class="svg-prev-icon" name="file-pdf-o" scale="5.0"/>
                                <icon v-else-if="fileTypeIs(item, 'text')" class="svg-prev-icon" name="file-text-o" scale="5.0"/>
                            </div>
                        </div>
                        <div class="card-content">
                            <p v-tippy="{arrow: true, hideOnClick: false, followCursor: true}"
                               :title="linkCopied ? trans('copied') : trans('to_cp')"
                               class="title is-marginless link"
                               @click="copyLink(item.path)"
                               @hidden="linkCopied = false">
                                {{ item.name }}
                            </p>
                            <br>
                            <p class="subtitle is-marginless link" @click="goToFolder(item.dir, item.name)">
                                <span class="icon"><icon name="folder"/></span>
                                <span v-tippy :title="trans('go_to_folder')">{{ item.dir }}</span>
                            </p>
                            <time>
                                <span class="icon"><icon name="clock-o"/></span>
                                <span>{{ item.last_modified_formated }}</span>
                            </time>
                        </div>
                    </div>
                </li>

                <!-- nothing found -->
                <li v-if="noData" key="noData" class="column no-data">
                    <p class="title">{{ trans('nothing_found') }} !!</p>
                </li>
            </transition-group>
        </div>
        <button class="modal-close is-large" @click="closePanel()"/>
    </div>
</template>

<style scoped lang="scss" src="../../../sass/modules/global-search.scss"></style>

<script>
import debounce from 'lodash/debounce'
import panels from '../../mixins/panels'

export default {
    components: {
        imageIntersect: require('./lazyLoading.vue')
    },
    directives: {
        VueInputAutowidth: require('vue-input-autowidth')
    },
    mixins: [panels],
    props: ['trans', 'fileTypeIs', 'noScroll', 'browserSupport'],
    data() {
        return {
            filesIndex: [],
            filterdList: [],
            search: '',
            noData: false,
            linkCopied: false,
            firstRun: false
        }
    },
    computed: {
        fuseLib() {
            return new Fuse(this.filesIndex, {
                keys: ['name'],
                threshold: 0.4
            })
        },
        listCount() {
            return this.filterdList.length
        }
    },
    methods: {
        orch() {
            setTimeout(() => {
                EventHub.fire('start-img-observing')
            }, 100)
        },
        eventsListener() {
            EventHub.listen('global-search-index', (data) => {
                this.filesIndex = data
            })

            EventHub.listen('show-global-search', (data) => {
                this.showPanel = true
            })

            EventHub.listen('clear-global-search', () => {
                this.search = ''
            })
        },
        copyLink(path) {
            this.linkCopied = true
            this.$copyText(path)
        },
        closePanel() {
            this.search = ''
            this.showPanel = false
        },
        goToFolder(dir, name) {
            EventHub.fire('search-go-to-folder', {
                dir: dir,
                name: name
            })
            this.closePanel()
        },
        getList: debounce(function () {
            let search = this.search

            if (search) {
                this.filterdList = this.fuseLib.search(search)
                return this.noData = this.listCount ? false : true
            }

            this.filterdList = []
        }, 500),
        ontransitionend() {
            this.noData = this.search && !this.listCount ? true : false
            this.firstRun = true
        }
    },
    watch: {
        showPanel(val) {
            this.showPanelWatcher(val)

            if (val) {
                this.$nextTick(() => {
                    this.$refs.search.focus()
                })
            } else {
                this.$nextTick(() => {
                    this.noData = false
                    this.firstRun = false
                })
            }
        },
        search(val) {
            this.getList()

            if (!this.firstRun) {
                this.$nextTick(() => {
                    this.firstRun = true
                })
            }
        },
        firstRun(val) {
            if (!val) {
                if (this.$refs['search-input']) this.$refs['search-input'].addEventListener('transitionend', this.ontransitionend)
            } else {
                this.$refs['search-input'].removeEventListener('transitionend', this.ontransitionend)
            }
        }
    },
    render() {}
}
</script>
