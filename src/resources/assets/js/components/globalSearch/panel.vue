<template>
    <div v-if="showSearchPanel" class="modal mm-animated fadeIn is-active">
        <div class="modal-background"/>
        <div class="modal-content">
            <div :class="{'move': moveInput}" class="search-input">
                <section>
                    <input ref="search" v-model="search" :placeholder="trans('find')" autofocus>
                    <transition name="mm-info-in">
                        <div v-show="listCount" class="count">
                            <p class="title is-marginless is-2">{{ listCount }}</p>
                            <p class="heading is-marginless">{{ trans('found') }}</p>
                        </div>
                    </transition>
                </section>
            </div>

            <transition-group name="mm-gs" tag="ul" class="columns is-multiline" mode="out-in">
                <li v-for="(item, i) in filterdList" :key="`${i}-${item.name}`" class="column is-2">
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

<style scoped lang="scss">
    @import '../../../sass/extra/vars';

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
        background-color: rgba($dark, 0.98);
    }

    .modal-content {
        margin: 0;
        height: 100%;
        width: 100%;
        max-height: unset;
    }

    .column {
        backface-visibility: hidden;
    }

    .search-input {
        transform: translate(25%, 35vh);
        width: 100%;
        position: sticky;
        top: 0;
        padding: 0 0.75rem;
        color: $white;
        z-index: 1;
        transition: all 1s ease 0.5s;

        // animate linear-gradient
        &::before {
            content: '';
            position: absolute;
            background: linear-gradient(to bottom, $dark 0, rgba($dark, 0.5) 65%, transparent 100%);
            opacity: 0;
            top: 0;
            transition: all 1s ease 1s;
            height: 100%;
            width: 100%;
        }

        section {
            display: flex;
            margin: 0 auto;
            width: 75%;
            position: relative;
            align-items: center;
        }

        input {
            transition: all 1s ease 0.5s;
            color: $white;
            line-height: 2.5;
            border: none;
            background-color: transparent;
            caret-color: $primary;
            font-size: 5rem;
        }

        .count {
            text-align: center;

            .title {
                color: $white;
                text-shadow: 4px 4px $red;
            }

            .heading {
                color: $white;
                font-weight: bold;
            }
        }

        &.move {
            transform: translate(0);

            &::before {
                opacity: 1;
            }

            input {
                font-size: 3rem;
                width: 100%;
            }
        }
    }

    .no-data {
        text-align: center;

        * {
            color: rgba($white, 0.5);
        }
    }

    .card {
        min-width: unset;
        border-radius: 4px;
    }

    .icon {
        vertical-align: middle;
    }

    @media screen and (max-width: 1083px) {
        .search-input {
            padding-right: 4rem;

            section {
                width: 100%;
            }

            input {
                line-height: 2;
            }
        }

        .modal-close {
            top: 1.5rem;
        }
    }
</style>

<script>
import debounce from 'lodash/debounce'

export default {
    props: ['trans', 'fileTypeIs', 'noScroll'],
    data() {
        return {
            showSearchPanel: false,
            filesIndex: [],
            filterdList: [],
            search: '',
            noData: false,
            moveInput: false,
            linkCopied: false
        }
    },
    mounted() {
        this.eventsListener()
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
        eventsListener() {
            EventHub.listen('global-search-index', (data) => {
                this.filesIndex = data
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

            document.addEventListener('transitionend', (e) => {
                this.noData = this.search && !this.listCount ? true : false
            })
        },

        copyLink(path) {
            this.linkCopied = true
            this.$copyText(path)
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
        }, 500)
    },
    watch: {
        showSearchPanel(val) {
            if (val) {
                this.$nextTick(() => {
                    this.noScroll('add')
                    this.$refs.search.focus()
                })

                EventHub.fire('disable-global-keys', true)
            } else {
                this.$nextTick(() => {
                    this.noScroll('remove')
                    this.noData = false
                    this.moveInput = false
                })

                EventHub.fire('disable-global-keys', false)
            }
        },
        search(val) {
            this.moveInput = true
            this.getList()

            document.addEventListener('transitionend', (e) => {
                this.noData = val && !this.listCount ? true : false
            })
        }
    }
}
</script>
