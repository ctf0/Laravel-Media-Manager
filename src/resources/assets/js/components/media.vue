<script>
import Utilities from '../modules/utils'
import Download from '../modules/download'
import Cache from '../modules/cache'
import Form from '../modules/form'
import ItemFiltration from '../modules/filtration'
import BulkSelect from '../modules/bulk'
import LockItem from '../modules/lock'
import ItemVisibility from '../modules/visibility'
import Selected from '../modules/selected'
import Restriction from '../modules/restriction'
import Image from '../modules/image'
import Url from '../modules/url'
import Watchers from '../modules/watch'
import Computed from '../modules/computed'
import Broadcasting from '../modules/broadcast'
import Scrolling from '../modules/scroll'

import Cropper from './imageEditor/cropper.vue'
import imageCache from './lazyLoading/cache.vue'
import imageIntersect from './lazyLoading/normal.vue'

export default {
    components: {Cropper, imageCache, imageIntersect},
    name: 'media-manager',
    mixins: [
        Utilities,
        Download,
        Cache,
        Form,
        ItemFiltration,
        BulkSelect,
        LockItem,
        ItemVisibility,
        Selected,
        Restriction,
        Computed,
        Watchers,
        Image,
        Url,
        Broadcasting,
        Scrolling
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
            no_files: false,
            loading_files: false,
            no_search: false,
            ajax_error: false,
            isLoading: false,
            toggleInfo: true,
            toggleUploadArea: false,
            showProgress: false,
            progressCounter: 0,

            linkCopied: false,
            bulkSelectAll: false,
            bulkSelect: false,
            folderWarning: false,
            checkForFolders: false,
            randomNames: false,
            useCopy: false,
            toolBar: true,
            imageWasEdited: false,

            files: [],
            folders: [],
            directories: [],
            filterdList: [],
            bulkList: [],
            lockedList: [],
            dimensions: [],

            moveToPath: null,
            selectedFile: null,
            currentFileIndex: null,
            sortBy: null,
            currentFilterName: null,
            searchItemsCount: null,
            searchFor: null,
            urlToUpload: null,
            newFolderName: null,
            newFilename: null,
            activeModal: null,

            imageSlideDirection: null,
            uploadPanelGradients: [
                'linear-gradient(141deg, #009e6c 0%, #00d1b2 71%, #00e7eb 100%)',
                'linear-gradient(141deg, #04a6d7 0%, #209cee 71%, #3287f5 100%)',
                'linear-gradient(141deg, #12af2f 0%, #23d160 71%, #2ce28a 100%)',
                'linear-gradient(141deg, #ffaf24 0%, #ffdd57 71%, #fffa70 100%)',
                'linear-gradient(141deg, #ff0561 0%, #ff3860 71%, #ff5257 100%)',
                'linear-gradient(141deg, #1f191a 0%, #363636 71%, #46403f 100%)'
            ],
            firstRun: true,
            firstMeta: false
        }
    },
    created() {
        window.addEventListener('popstate', this.urlNavigation)
        document.addEventListener('keydown', this.shortCuts)
        this.init()
    },
    mounted() {
        // check if image was edited
        EventHub.listen('image-edited', (msg) => {
            this.imageWasEdited = true
            this.$refs['success-audio'].play()
            this.removeCachedResponse().then(() => {
                this.showNotif(`${this.trans('save_success')} "${msg}"`)
            })
        })

        // get images dimensions
        EventHub.listen('save-image-dimensions', (obj) => {
            this.dimensions.push(obj)
        })
    },
    updated() {
        this.autoPlay()
        this.$nextTick(() => {
            this.activeModal || this.inModal
                ? this.noScroll('add')
                : this.noScroll('remove')

            return this.checkForFolders = this.$refs.move_folder_dropdown.options[0]
                ? true
                : false
        })
    },
    beforeDestroy() {
        window.removeEventListener('popstate', this.urlNavigation)
        document.removeEventListener('keydown', this.shortCuts)
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
        shortCuts(e) {
            let key = keycode(e)

            if (!(this.isLoading || e.altKey || e.ctrlKey || e.metaKey)) {
                // when modal isnt visible
                if (!this.activeModal) {
                    // when search is not focused
                    if (document.activeElement.dataset.search == undefined) {
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
                                    if (this.selectedFileIs('video') || this.selectedFileIs('audio')) {
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

                            // hide upload panel
                            if (this.toggleUploadArea && key == 'esc') {
                                this.toggleUploadPanel()
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
                        if (key == 't') {
                            this.toggleInfoPanel()
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
        },

        /**
         * autoplay media
         */
        autoPlay() {
            if (this.filterNameIs('audio') || this.filterNameIs('video')) {
                let player = this.$refs.player
                player.onended = () => {
                    // stop at the end of list
                    if (this.currentFileIndex < this.allItemsCount - 1) {
                        // nav to next
                        this.goToNext()

                        // play navigated to
                        this.$nextTick(() => {
                            this.$refs.player.play()
                        })
                    }
                }
            }
        }
    },
    render() {}
}
</script>
