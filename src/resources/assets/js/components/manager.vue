<style lang="scss" scoped src="../../sass/packages/bulma.scss"></style>

<script>
import debounce from 'lodash/debounce'
import Vue2Filters from 'vue2-filters'

import Broadcast from '../modules/broadcast'
import BulkSelect from '../modules/bulk'
import Cache from '../modules/cache'
import Computed from '../modules/computed'
import Download from '../modules/download'
import Form from '../modules/form'
import Folder from '../modules/folder'
import Gestures from '../modules/gestures'
import Image from '../modules/image'
import ItemFiltration from '../modules/filtration'
import ItemVisibility from '../modules/visibility'
import LockItem from '../modules/lock'
import Movable from '../modules/movable'
import MediaPlayer from '../modules/media_player'
import Restriction from '../modules/restriction'
import Scroll from '../modules/scroll'
import Selection from '../modules/selection'
import Url from '../modules/url'
import Upload from '../modules/upload'
import Utilities from '../modules/utils'
import Watchers from '../modules/watch'

export default {
    components: {
        contentRatio: require('./utils/ratio.vue').default,
        globalSearchBtn: require('./globalSearch/button.vue').default,
        globalSearchPanel: require('./globalSearch/panel.vue').default,
        imageEditor: require('./image/editor/main.vue').default,
        imageIntersect: require('./image/lazyLoading.vue').default,
        imagePreview: require('./image/preview.vue').default,
        usageIntroOverlay: require('./usageIntro/overlay.vue').default,
        usageIntroBtn: require('./usageIntro/button.vue').default,
        usageIntroPanel: require('./usageIntro/panel.vue').default,
        uploadPreview: require('./utils/upload-preview.vue').default,
        InfiniteLoading: require('vue-infinite-loading').default,
        filterAndSorting: require('./toolbar/filter-sort.vue').default,
        dirBookmarks: require('./toolbar/dir-bookmark.vue').default
    },
    name: 'media-manager',
    mixins: [
        Vue2Filters.mixin,
        Broadcast,
        BulkSelect,
        Cache,
        Computed,
        Download,
        Form,
        Folder,
        Gestures,
        Image,
        ItemFiltration,
        ItemVisibility,
        LockItem,
        Movable,
        MediaPlayer,
        Restriction,
        Scroll,
        Selection,
        Url,
        Upload,
        Utilities,
        Watchers
    ],
    props: [
        'config',
        'routes',
        'inModal',
        'translations',
        'uploadPanelImgList',
        'hideExt',
        'hidePath',
        'restrict',
        'userId'
    ],
    data() {
        return {
            ajax_error: false,
            bulkSelect: false,
            bulkSelectAll: false,
            disableShortCuts: false,
            firstMeta: false, // for alt + click selection
            firstRun: false, // deffer running logic on init
            folderDeleteWarning: false,
            imageWasEdited: false,
            infoSidebar: false,
            introIsOn: false,
            isLoading: false,
            linkCopied: false,
            loading_files: false,
            no_files: false,
            no_search: false,
            useRandomNamesForUpload: false,
            showProgress: false,
            isASmallScreen: false,
            toolBar: true,
            uploadArea: false,
            waitingForUpload: false,
            copyFilesNotMove: false,
            uploadPreviewOptionsPanelIsVisible: false,
            globalSearchPanelIsVisible: false,

            activeModal: null,
            currentFileIndex: null,
            filterName: null,
            sortName: null,
            imageSlideDirection: null,
            newFilename: null,
            newFolderName: null,
            searchFor: null,
            searchItemsCount: null,
            selectedFile: null,
            global_search_item: null,
            urlToUpload: null,
            selectedUploadPreviewName: null,

            audioFileMeta: {},
            restrictions: Object.assign({
                'path': null,
                'uploadTypes': null,
                'uploadSize': null
            }, this.restrict),
            movableList: [],
            bulkList: [],
            dimensions: [],
            files: [],
            filterdFilesList: [],
            folders: [],
            uploadPreviewList: [],
            uploadPreviewNamesList: [],
            uploadPreviewOptionsList: [],
            dirBookmarks: [],
            player: {
                item: null,
                fs: false,
                playing: false
            },
            lockedList: [],
            uploadPanelGradients: [
                'linear-gradient(141deg, #009e6c 0, #00d1b2 71%, #00e7eb 100%)',
                'linear-gradient(141deg, #04a6d7 0, #209cee 71%, #3287f5 100%)',
                'linear-gradient(141deg, #12af2f 0, #23d160 71%, #2ce28a 100%)',
                'linear-gradient(141deg, #ffaf24 0, #ffdd57 71%, #fffa70 100%)',
                'linear-gradient(141deg, #ff0561 0, #ff3860 71%, #ff5257 100%)',
                'linear-gradient(141deg, #1f191a 0, #363636 71%, #46403f 100%)'
            ],
            progressCounter: 0,
            scrollByRowItemsCount: 0
        }
    },
    created() {
        // confirm file pending upload cancel
        window.addEventListener('beforeunload', (event) => {
            if (this.showProgress) {
                event.preventDefault()
                event.returnValue = 'Current Upload Will Be Canceled !!'
            }
        })
        window.addEventListener('unload', (event) => {
            if (this.showProgress) {
                EventHub.fire('clear-pending-upload')
            }
        })

        // rest
        window.addEventListener('resize', this.onResize)
        window.addEventListener('popstate', this.urlNavigation)
        document.addEventListener('keydown', this.shortCuts)
    },
    mounted() {
        this.init()
        this.eventsListener()
    },
    updated: debounce(function() {
        if (this.firstRun) {
            this.updateScrollByRow()

            if (this.selectedFileIs('video') || this.selectedFileIs('audio')) {
                this.destroyPlyr()
                this.$nextTick(() => this.initPlyr())
            }

            if (!this.introIsOn) {
                this.activeModal || this.inModal
                    ? this.noScroll('add')
                    : this.noScroll('remove')
            }
        }
    }, 250),
    beforeDestroy() {
        window.removeEventListener('resize', this.onResize)
        window.removeEventListener('popstate', this.urlNavigation)
        document.removeEventListener('keydown', this.shortCuts)
        this.destroyPlyr()
        this.noScroll('remove')
    },
    methods: {
        init() {
            // small screen stuff
            if (this.checkForSmallScreen()) {
                this.applySmallScreen()
            }

            // restrictions
            EventHub.listen('external_modal_resrtict', (data) => {
                return this.restrictions = Object.assign(this.restrictions, data)
            })

            if (this.restrictModeIsOn) {
                this.clearUrlQuery()
                this.resolveRestrictFolders()

                return this.getFiles().then(this.afterInit())
            }

            // normal
            this.getPathFromUrl()
                    .then(this.preSaved())
                    .then(this.getFiles(null, this.selectedFile))
                    .then(this.updatePageUrl())
                    .then(this.afterInit())

        },
        afterInit() {
            this.fileUpload()
            this.$nextTick(() => {
                this.onResize()
                this.firstRun = true
            })
        },
        eventsListener() {
            // check if image was edited
            EventHub.listen('image-edited', (msg) => {
                this.imageWasEdited = true
                this.showNotif(`${this.trans('save_success')} "${msg}"`, 'success', 5)
            })

            // get images dimensions
            EventHub.listen('save-image-dimensions', (obj) => {
                if (!this.checkForDimensions(obj.url)) {
                    this.dimensions.push(obj)
                }
            })

            // stop listening to shortcuts
            EventHub.listen('disable-global-keys', (data) => this.disableShortCuts = data)

            // global-search
            EventHub.listen('toggle-global-search', (data) => this.globalSearchPanelIsVisible = data)

            EventHub.listen('global-search-go-to-folder', (data) => {
                this.folders = this.arrayFilter(data.dir.split('/'))

                return this.getFiles(null, data.name).then(this.updatePageUrl())
            })

            EventHub.listen('global-search-delete-item', (data) => {
                this.global_search_item = data

                this.fileTypeIs(data, 'folder')
                    ? this.folderDeleteWarning = true
                    : this.folderDeleteWarning = false

                this.deleteItem()
            })

            // bookmark
            EventHub.listen('dir-bookmarks-update', (data) => this.dirBookmarks = data)
        },

        shortCuts(e) {
            let key = keycode(e)

            if (!(this.isLoading || e.altKey || e.ctrlKey || e.metaKey || this.disableShortCuts)) {
                // when modal isnt visible
                if (!this.activeModal && !this.waitingForUpload) {
                    // when search is not focused
                    if (!this.isFocused('search', e)) {
                        // when no bulk selecting
                        if (!this.isBulkSelecting()) {
                            // open folder
                            if (key == 'enter' && this.selectedFile) {
                                this.openFolder(this.selectedFile)
                            }

                            // go up a dir
                            if (key == 'backspace' && this.folders.length) {
                                e.preventDefault()
                                this.goToPrevFolder()
                            }

                            // when there are files
                            if (this.allItemsCount) {
                                this.navigation(e)

                                if (key == 'space' &&
                                    e.target == document.body &&
                                    (
                                        this.selectedFileIs('video') ||
                                        this.selectedFileIs('audio') ||
                                        this.selectedFileIs('image') ||
                                        this.selectedFileIs('pdf') ||
                                        this.textFileType()
                                    )
                                ) {
                                    e.preventDefault()

                                    // play-pause media
                                    if (this.selectedFileIs('video') || this.selectedFileIs('audio')) {
                                        this.infoSidebar
                                            ? this.playMedia()
                                            : this.toggleModal('preview_modal')
                                    }

                                    // "show" image/pdf/text quick view
                                    if (this.selectedFileIs('image') || this.selectedFileIs('pdf') || this.textFileType()) {
                                        this.toggleModal('preview_modal')
                                    }
                                }

                                // image editor
                                if (key == 'e') {
                                    this.$refs.editor.click()
                                }
                            }
                            // end of when there are files

                            // refresh
                            if (key == 'r') {
                                if (!this.$refs.refresh.$el.disabled && !this.isLoading) {
                                    this.refresh()
                                }
                            }

                            // file upload
                            if (key == 'u') {
                                this.$refs.upload.click()
                            }
                        }
                        // end of no bulk selection

                        // we have files
                        if (this.allItemsCount) {
                            // bulk select
                            if (key == 'b') {
                                if (this.searchFor && this.searchItemsCount == 0) return

                                this.$refs.bulkSelect.click()
                            }

                            if (this.isBulkSelecting()) {
                                // add all to bulk list
                                if (key == 'a') {
                                    this.$refs.bulkSelectAll.click()
                                }

                                // cancel bulk selection
                                if (key == 'esc') {
                                    this.$refs.bulkSelect.click()
                                }
                            }

                            // delete file
                            if (key == 'delete' || key == 'd') {
                                this.$refs.delete.click()
                            }

                            // copy file
                            if (key == 'c' || key == 'x') {
                                this.addToMovableList()
                            }

                            // lock files
                            if (key == 'l') {
                                this.$refs.lock.click()
                            }

                            // set visibility
                            if (key == 'v') {
                                this.$refs.visibility.click()
                            }
                        }
                        // end of we have files

                        // reset stuff
                        if (key == 'esc') {
                            // hide upload panel
                            if (this.uploadArea) {
                                this.toggleUploadPanel()
                            }

                            // clear filter & sort
                            this.resetInput(['filterName', 'sortName'])
                        }

                        // move file
                        if ((key == 'm' || key == 'p') && this.movableItemsCount) {
                            this.showMovableList()
                        }

                        // toggle file details sidebar
                        if (key == 't' && !this.isASmallScreen) {
                            this.toggleInfoSidebar()
                            this.saveUserPref()
                        }
                    }
                    // end of search is not focused

                    // cancel search
                    else if (key == 'esc') {
                        this.resetInput('searchFor')
                    }
                }
                // end of modal isnt visible

                // when modal is visible
                else {
                    if (this.isActiveModal('preview_modal')) {
                        if (key == 'space') {
                            this.selectedFileIs('video') || this.selectedFileIs('audio')
                                ? this.playMedia()
                                : this.toggleModal()
                        }

                        this.navigation(e)
                    }

                    // hide modal
                    if (key == 'esc' && !this.isActiveModal('imageEditor_modal')) {
                        this.toggleModal()
                    }
                }
                // end of modal is visible

                // when upload preview is visible
                if (this.waitingForUpload && !this.uploadPreviewOptionsPanelIsVisible) {
                    // proceed with upload
                    if (key == 'enter') {
                        this.$refs['process-dropzone'].click()
                    }

                    // clear upload queue
                    if (key == 'esc') {
                        if (this.uploadArea) {
                            return this.toggleUploadPanel()
                        }

                        this.$refs['clear-dropzone'].click()
                        this.waitingForUpload = false
                    }

                    // trigger upload panel
                    if (key == 'u') {
                        this.$refs.upload.click()
                    }
                }
            }
        },
        // end of short cuts

        refresh() {
            EventHub.fire('clear-global-search')
            this.resetInput('searchFor')

            return this.getFiles(null, this.selectedFile ? this.selectedFile.name : null)
        },
        clearAll() {
            if (!this.isLoading) {
                this.clearUrlQuery()
                this.clearLs()
                this.ajaxError(false)
                this.showNotif('Cache Cleared')
            }
        },
        moveItem() {
            this.$nextTick(() => {
                if (this.$refs.move.disabled) return

                this.toggleModal('move_file_modal')
            })
        },
        renameItem() {
            this.$nextTick(() => {
                if (this.$refs.rename.disabled) return

                this.toggleModal('rename_file_modal')
            })
        },
        deleteItem() {
            this.$nextTick(() => {
                if (!this.globalSearchPanelIsVisible) {
                    if (this.$refs.delete.disabled) return

                    if (!this.isBulkSelecting() && this.selectedFile) {
                        this.selectedFileIs('folder')
                            ? this.folderDeleteWarning = true
                            : this.folderDeleteWarning = false
                    }

                    if (this.bulkItemsCount) {
                        this.bulkItemsFilter.some((item) => {
                            if (this.fileTypeIs(item, 'folder')) {
                                return this.folderDeleteWarning = true
                            }
                        })
                    }
                }

                this.toggleModal('confirm_delete_modal')
            })
        },
        createNewFolder() {
            this.toggleModal('new_folder_modal')
        }
    },
    render() {}
}
</script>
