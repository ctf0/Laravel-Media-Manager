<template>
    <div v-if="showSearchPanel" class="modal mm-animated fadeIn is-active">
        <div class="modal-background"/>
        <div class="modal-content">
            <div class="search-input">
                <section>
                    <input ref="search" v-model="search" :placeholder="trans('find')" autofocus>
                    <span class="icon is-medium"><icon name="search"/></span>
                </section>
            </div>

            <transition-group tag="ul" mode="out-in" name="list" class="columns is-multiline">
                <li v-for="(item, i) in filterdList" :key="i" class="column is-2">
                    <div class="card">
                        <div class="card-image">
                            <a v-if="fileTypeIs(item, 'image')" :href="item.path" target="_blank" class="image">
                                <img :src="item.path" :alt="item.name">
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
                            <p class="title is-marginless">{{ item.name }}</p>
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
            </transition-group>
        </div>
        <button class="modal-close is-large" @click="closePanel()"/>
    </div>
</template>

<style scoped lang="scss">
    .modal {
        position: absolute;
        width: 100%;
        height: 100%;
        align-items: start;
        padding: 0.75rem;
        padding-top: 0;
    }

    .modal-background {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        background-color: rgba(10, 10, 10, 0.98);
    }

    .modal-content {
        margin: 0;
        height: 100%;
        width: 100%;
        max-height: 100vh;
    }

    .search-input {
        width: 100%;
        position: sticky;
        top: 0;
        color: rgba(255, 255, 255, 0.8);
        z-index: 1;
        padding: 1rem 0;
        background: rgba(15, 15, 15, 0.85);
        backdrop-filter: blur(10px);

        section {
            margin-right: auto;
            margin-left: 5%;
            display: flex;
            padding: 0.2rem 1rem;
            width: 25%;
            position: relative;
            border-radius: 100vw;
            background-color: rgba(white, 0.1);

            input {
                color: white;
                border: none;
                width: 100%;
                background-color: transparent;
                font-size: 1.5rem;
            }
        }
    }

    .card {
        min-width: unset;
        border-radius: 4px;
    }

    .icon {
        vertical-align: middle;
    }

    .modal-close {
        position: absolute;
        z-index: 2;
        top: 1.2rem;
    }

    @media screen and (max-width: 1083px) {
        .search-input {
            padding-right: 3rem;

            section {
                width: 100%;
                margin: auto;
            }
        }
    }
</style>

<script>
import debounce from 'lodash/debounce'

export default {
    props: ['trans', 'fileTypeIs'],
    data() {
        return {
            showSearchPanel: false,
            index: [],
            filterdList: [],
            search: ''
        }
    },
    mounted() {
        this.eventsListener()
    },
    computed: {
        fuseLib() {
            return new Fuse(this.index, {keys: ['name']})
        }
    },
    methods: {
        eventsListener() {
            EventHub.listen('global-search-index', (data) => {
                this.index = data
            })

            EventHub.listen('show-global-search', (data) => {
                this.showSearchPanel = true
            })

            EventHub.listen('hide-global-search', () => {
                this.closePanel()
            })

            EventHub.listen('clear-global-search', () => {
                this.search = ''
            })

            document.addEventListener('keydown', (e) => {
                if (keycode(e) == 'esc') {
                    this.closePanel()
                }
            })
        },
        closePanel() {
            this.showSearchPanel = false
            this.search = ''
        },
        goToFolder(dir, name) {
            EventHub.fire('search-go-to-folder', {
                dir: dir,
                name: name
            })
        },
        getList: debounce(function () {
            let search = this.search

            if (search) {
                return this.filterdList = this.fuseLib.search(search)
            }

            this.filterdList = []
        }, 250)
    },
    watch: {
        showSearchPanel(val) {
            let cont = document.querySelector('#manager-container')

            if (val) {
                EventHub.fire('disable-global-keys', true)
                this.$nextTick(() => {
                    this.$refs.search.focus()
                })

                // cont.style.position = 'relative'
            } else {
                EventHub.fire('disable-global-keys', false)
                // cont.style.position = ''
            }
        },
        search() {
            this.getList()
        }
    }
}
</script>
