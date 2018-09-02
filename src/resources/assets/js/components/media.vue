<style lang="scss" scoped src="../../sass/packages/bulma.scss"></style>

<script>
import debounce from 'lodash/debounce'

import Broadcast from '../modules/broadcast'
import BulkSelect from '../modules/bulk'
import Cache from '../modules/cache'
import Computed from '../modules/computed'
import Download from '../modules/download'
import Form from '../modules/form'
import Image from '../modules/image'
import ItemFiltration from '../modules/filtration'
import ItemVisibility from '../modules/visibility'
import LockItem from '../modules/lock'
import MediaPlayer from '../modules/media-player'
import Restriction from '../modules/restriction'
import Scroll from '../modules/scroll'
import Selection from '../modules/selection'
import Url from '../modules/url'
import Utilities from '../modules/utils'
import Watchers from '../modules/watch'

export default {
    components: {
        contentRatio: require('./ratio.vue'),
        cropper: require('./imageEditor/cropper.vue'),
        globalSearchBtn: require('./globalSearch/button.vue'),
        globalSearchPanel: require('./globalSearch/panel.vue'),
        imageCache: require('./lazyLoading/cache.vue'),
        imageIntersect: require('./lazyLoading/normal.vue')
    },
    name: 'media-manager',
    mixins: [
        Broadcast,
        BulkSelect,
        Cache,
        Computed,
        Download,
        Form,
        Image,
        ItemFiltration,
        ItemVisibility,
        LockItem,
        MediaPlayer,
        Restriction,
        Scroll,
        Selection,
        Url,
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
            checkForFolders: false,
            disableShortCuts: false,
            folderWarning: false,
            imageWasEdited: false,
            isLoading: false,
            linkCopied: false,
            loading_files: false,
            no_files: false,
            no_search: false,
            randomNames: false,
            showProgress: false,
            infoSidebar: false,
            playerCard: false,
            UploadArea: false,
            toolBar: true,
            useCopy: false,
            firstMeta: false,  // for alt + click selection
            firstRun: true,    // for delayed scroll on manager init

            progressCounter: 0,
            scrollByRows: 0,

            searchFor: null,
            searchItemsCount: null,
            selectedFile: null,
            sortBy: null,
            urlToUpload: null,
            activeModal: null,
            currentFileIndex: null,
            currentFilterName: null,
            imageSlideDirection: null,
            moveToPath: null,
            newFilename: null,
            newFolderName: null,
            plyr: null,

            files: [],
            folders: [],
            directories: [],
            filterdList: [],
            bulkList: [],
            lockedList: [],
            dimensions: [],

            uploadPanelGradients: [
                'linear-gradient(141deg, #009e6c 0, #00d1b2 71%, #00e7eb 100%)',
                'linear-gradient(141deg, #04a6d7 0, #209cee 71%, #3287f5 100%)',
                'linear-gradient(141deg, #12af2f 0, #23d160 71%, #2ce28a 100%)',
                'linear-gradient(141deg, #ffaf24 0, #ffdd57 71%, #fffa70 100%)',
                'linear-gradient(141deg, #ff0561 0, #ff3860 71%, #ff5257 100%)',
                'linear-gradient(141deg, #1f191a 0, #363636 71%, #46403f 100%)'
            ]
        }
    },
    created() {
        window.addEventListener('popstate', this.urlNavigation)
        window.addEventListener('resize', this.onResize)
        document.addEventListener('keydown', this.shortCuts)
        this.init()
    },
    mounted() {
        this.onResize()
        this.eventsListener()

        this.$nextTick(debounce(() => {
            this.scrollByRow()
        }, 1000))
    },
    updated: debounce(function() {
        this.initPlyr()

        this.activeModal || this.inModal
            ? this.noScroll('add')
            : this.noScroll('remove')

        return this.checkForFolders = this.$refs.move_folder_dropdown.options[0]
            ? true
            : false
    }, 250),
    beforeDestroy() {
        window.removeEventListener('popstate', this.urlNavigation)
        document.removeEventListener('keydown', this.shortCuts)
        this.destroyPlyr()
        this.noScroll('remove')
    },
    methods: {
        init() {
            // restricted
            if (this.restrictModeIsOn()) {
                this.clearUrlQuery()
                this.resolveRestrictFolders()

                return this.getFiles(this.folders).then(() => {
                    this.firstRun = false
                    this.fileUpload()
                })
            }

            // normal
            this.getPathFromUrl().then(() => {
                return this.preSaved()
            }).then(() => {
                return this.getFiles(this.folders, null, this.selectedFile).then(() => {
                    this.firstRun = false
                    this.fileUpload()
                })
            })
        },

        eventsListener() {
            // check if image was edited
            EventHub.listen('image-edited', (msg) => {
                this.imageWasEdited = true
                this.removeCachedResponse().then(() => {
                    this.showNotif(`${this.trans('save_success')} "${msg}"`)
                })
            })

            // get images dimensions
            EventHub.listen('save-image-dimensions', (obj) => {
                this.dimensions.push(obj)
            })

            EventHub.listen('disable-global-keys', (val) => {
                this.disableShortCuts = val
            })

            EventHub.listen('search-go-to-folder', (data) => {
                EventHub.fire('hide-global-search')
                this.folders = this.arrayFilter(data.dir.split('/'))

                return this.getFiles(this.folders, null, data.name).then(() => {
                    this.updatePageUrl()
                })
            })
        },

        shortCuts(e) {
            let key = keycode(e)

            if (!(this.isLoading || e.altKey || e.ctrlKey || e.metaKey || this.disableShortCuts)) {
                // when modal isnt visible
                if (!this.activeModal) {
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

                                if (key == 'space' && e.target == document.body && (
                                    this.selectedFileIs('video') ||
                                    this.selectedFileIs('audio') ||
                                    this.selectedFileIs('image') ||
                                    this.selectedFileIs('pdf') ||
                                    this.selectedFileIs('text')
                                )) {
                                    e.preventDefault()

                                    // play-pause media
                                    if (
                                        !this.playerCard &&
                                        (this.selectedFileIs('video') || this.selectedFileIs('audio'))
                                    ) {
                                        this.playMedia()
                                    }

                                    // "show" image/pdf/text quick view
                                    if (this.selectedFileIs('image') || this.selectedFileIs('pdf') || this.selectedFileIs('text')) {
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

                            if (key == 'esc') {
                                // hide upload panel
                                if (this.UploadArea) {
                                    this.toggleUploadPanel()
                                }

                                // clear filter
                                if (this.currentFilterName) {
                                    this.showFilesOfType('all')
                                }
                            }
                        }
                        /* end of no bulk selection */

                        // we have files
                        if (this.allItemsCount) {
                            // bulk select
                            if (key == 'b') {
                                if (this.searchFor && this.searchItemsCount == 0) {
                                    return
                                }

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

                            // move file
                            if (this.checkForFolders && key == 'm') {
                                this.$refs.move.click()
                            }

                            // lock files
                            if (key == 'l') {
                                this.$refs.lock.click()
                            }

                            // set visibility
                            if (key == 'v') {
                                this.$refs.vis.click()
                            }
                        }
                        /* end of we have files */

                        // toggle file details sidebar
                        if (key == 't' && !this.playerCard) {
                            this.toggleInfoSidebar()
                            this.saveUserPref()
                        }
                    }
                    /* end of search is not focused */

                    // cancel search
                    else if (key == 'esc') {
                        this.resetInput('searchFor')
                    }
                }
                /* end of modal isnt visible */

                // when modal is visible
                else {
                    if (this.isActiveModal('preview_modal')) {
                        if (key == 'space') {
                            e.preventDefault()
                            this.toggleModal()
                        }

                        this.navigation(e)
                    }

                    if (key == 'esc') {
                        this.toggleModal()
                    }
                }
                /* end of modal is visible */
            }
        },
        /* end of short cuts */

        refresh() {
            EventHub.fire('clear-global-search')
            this.resetInput('searchFor')
            return this.getFiles(this.folders, null, this.selectedFile ? this.selectedFile.name : null)
        },
        moveItem() {
            if (this.$refs.move.disabled) {
                return
            }

            this.toggleModal('move_file_modal')
        },
        renameItem() {
            this.toggleModal('rename_file_modal')
        },
        deleteItem() {
            if (this.$refs.delete.disabled) {
                return
            }

            if (!this.isBulkSelecting() && this.selectedFile) {
                this.selectedFileIs('folder')
                    ? this.folderWarning = true
                    : this.folderWarning = false
            }

            if (this.bulkItemsCount) {
                this.bulkItemsFilter.some((item) => {
                    return this.fileTypeIs(item, 'folder')
                        ? this.folderWarning = true
                        : this.folderWarning = false
                })
            }

            this.toggleModal('confirm_delete_modal')
        }
    },
    render() {}
}
</script>
