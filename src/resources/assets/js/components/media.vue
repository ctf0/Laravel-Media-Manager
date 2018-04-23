<script>
import Utilities from '../modules/utils'
import Download from '../modules/download'
import Cache from '../modules/cache'
import Form from '../modules/form'
import FileFiltration from '../modules/filtration'
import BulkSelect from '../modules/bulk'
import LockFile from '../modules/lock'
import SelectedFile from '../modules/selected'
import Restriction from '../modules/restriction'
import Watchers from '../modules/watch'
import Computed from '../modules/computed'
import Image from '../modules/image'

import Bounty from 'vue-bounty'
import Cropper from './imageEditor/cropper.vue'

export default {
    components: {Bounty, Cropper},
    name: 'media-manager',
    mixins: [
        Utilities,
        Download,
        Cache,
        Form,
        FileFiltration,
        BulkSelect,
        LockFile,
        SelectedFile,
        Restriction,
        Computed,
        Watchers,
        Image
    ],
    props: [
        'config',
        'inModal',
        'translations',
        'filesRoute',
        'dirsRoute',
        'lockFileRoute',
        'zipProgressRoute',
        'uploadPanelImgList',
        'hideExt',
        'hidePath',
        'cacheExp'
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
            visibilityType: 'public',
            imageWasEdited: false,

            files: [],
            folders: [],
            directories: [],
            filterdList: [],
            bulkList: [],
            lockedList: [],

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
            ]
        }
    },
    created() {
        document.addEventListener('keydown', this.shortCuts)

        this.invalidateCache().then(() => {
            this.preSaved()
            this.getFiles(this.folders, null, this.selectedFile)
        })

    },
    mounted() {
        this.fileUpload()

        // check if image was edited
        EventHub.listen('image-edited', () => {
            this.imageWasEdited = true
        })
    },
    updated() {
        this.autoPlay()
        this.$nextTick(() => {
            return this.$refs.move_folder_dropdown.options[0]
                ? this.checkForFolders = true
                : this.checkForFolders = false
        })
    },
    beforeDestroy() {
        document.removeEventListener('keydown', this.shortCuts)
    },
    methods: {
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

                                if (
                                    key == 'space' &&
                                    e.target == document.body &&
                                    (
                                        this.selectedFileIs('video') ||
                                        this.selectedFileIs('audio') ||
                                        this.selectedFileIs('image') ||
                                        this.selectedFileIs('pdf') ||
                                        this.selectedFileIs('text')
                                    )
                                ) {
                                    e.preventDefault()

                                    // play-pause media
                                    if (this.selectedFileIs('video') || this.selectedFileIs('audio')) {
                                        let player = this.$refs.player

                                        return player.paused
                                            ? player.play()
                                            : player.pause()
                                    }

                                    // "show" image/pdf/text quick view
                                    if (this.selectedFileIs('image') || this.selectedFileIs('pdf') || this.selectedFileIs('text')) {
                                        this.noScroll('add')
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
                        /* end of no bulk selection */

                        // with or without bulk selection
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
                        /* end of with or without bulk selection */

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
                        // hide lb
                        if (key == 'space') {
                            e.preventDefault()
                            this.toggleModal()
                        }

                        this.navigation(e)
                    }

                    // hide lb
                    if (key == 'esc') {
                        this.toggleModal()
                    }
                }
                /* end of modal is visible */
            }
        },
        /* end of short cuts */

        refresh() {
            this.getFiles(this.folders, null, this.selectedFile ? this.selectedFile.name : null)
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
                this.bulkListFilter.some((item) => {
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
                if (player) {
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
        }
    },
    render() {}
}
</script>
