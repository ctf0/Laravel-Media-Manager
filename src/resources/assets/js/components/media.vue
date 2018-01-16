<script>
import Utilities from './modules/utils'
import Download from './modules/download'
import Cache from './modules/cache'
import Form from './modules/form'
import FileFiltration from './modules/filtration'
import BulkSelect from './modules/bulk'
import LockFile from './modules/lock'
import SelectedFile from './modules/selected'
import Restriction from './modules/restriction'
import Watchers from './modules/watch'
import Computed from './modules/computed'

import Bounty from 'vue-bounty'
import Cropper from './ImageEditor/cropper.vue'

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
        Watchers
    ],
    props: [
        'baseUrl',
        'inModal',
        'hideFilesExt',
        'mediaTrans',
        'filesRoute',
        'dirsRoute',
        'lockFileRoute',
        'zipProgressRoute',
        'restrictPath',
        'uploadPanelImgList',
        'hideExt',
        'hidePath'
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
        this.preSaved()

        if (this.checkForRestrictedPath()) {
            return this.restrictAccess()
        }

        this.getFiles(this.folders, null, this.selectedFile)

        document.addEventListener('keydown', this.shortCuts)
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
        preSaved() {
            let ls = this.getLs()

            if (ls) {
                this.randomNames = ls.randomNames === undefined ? false : ls.randomNames
                this.folders = ls.folders === undefined ? [] : ls.folders
                this.toolBar = ls.toolBar === undefined ? true : ls.toolBar
                this.selectedFile = ls.selectedFileName === undefined ? null : ls.selectedFileName
            }
        },

        shortCuts(e) {
            if (!(this.isLoading || e.altKey || e.ctrlKey || e.metaKey)) {
                // when modal isnt visible
                if (!this.activeModal) {
                    // when search is not focused
                    if (document.activeElement.dataset.search == undefined) {
                        // when no bulk selecting
                        if (!this.isBulkSelecting()) {

                            // open folder
                            if (keycode(e) == 'enter' && this.selectedFile) {
                                this.openFolder(this.selectedFile)
                            }

                            // go up a dir
                            if (keycode(e) == 'backspace' && this.folders.length) {
                                this.goToPrevFolder()
                            }

                            // when there are files
                            if (this.allItemsCount) {
                                this.navigation(e)

                                if (
                                    keycode(e) == 'space' &&
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
                                if (keycode(e) == 'e') {
                                    this.imageEditor()
                                }
                            }
                            // end of when there are files

                            // refresh
                            if (keycode(e) == 'r') {
                                this.refresh()
                            }

                            // file upload
                            if (keycode(e) == 'u') {
                                this.$refs.upload.click()
                            }
                        }
                        /* end of no bulk selection */

                        // with or without bulk selection
                        if (this.allItemsCount) {
                            // bulk select
                            if (keycode(e) == 'b') {
                                if (this.searchFor && this.searchItemsCount == 0) {
                                    return
                                }

                                if (this.$refs.bulkSelect) {
                                    this.$refs.bulkSelect.click()
                                }
                            }

                            if (this.isBulkSelecting()) {
                                // add all to bulk list
                                if (keycode(e) == 'a') {
                                    if (this.$refs.bulkSelectAll) {
                                        this.$refs.bulkSelectAll.click()
                                    }
                                }

                                // cancel bulk selection
                                if (keycode(e) == 'esc') {
                                    if (this.$refs.bulkSelect) {
                                        this.$refs.bulkSelect.click()
                                    }
                                }
                            }

                            // delete file
                            if (keycode(e) == 'delete' || keycode(e) == 'd') {
                                if (!this.$refs.delete[0].disabled) {
                                    this.$refs.delete[0].click()
                                }
                            }

                            // move file
                            if (this.checkForFolders && keycode(e) == 'm') {
                                if (!this.$refs.move[0].disabled) {
                                    this.$refs.move[0].click()
                                }
                            }

                            // lock files
                            if (keycode(e) == 'l') {
                                if (this.$refs.lock) {
                                    this.$refs.lock.click()
                                }
                            }
                        }
                        /* end of with or without bulk selection */

                        // toggle file details sidebar
                        if (keycode(e) == 't') {
                            this.toggleInfoPanel()
                        }
                    }
                    /* end of search is not focused */

                    // cancel search
                    else if (keycode(e) == 'esc') {
                        this.resetInput('searchFor')
                    }
                }
                /* end of modal isnt visible */

                // when modal is visible
                else {
                    if (this.isActiveModal('preview_modal')) {
                        // hide lb
                        if (keycode(e) == 'space') {
                            e.preventDefault()
                            this.toggleModal()
                        }

                        this.navigation(e)
                    }

                    // hide lb
                    if (keycode(e) == 'esc') {
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
            if (this.$refs.move[0].disabled) {
                return
            }

            this.toggleModal('move_file_modal')
        },
        renameItem() {
            this.toggleModal('rename_file_modal')
        },
        imageEditor() {
            return this.$refs['image_editor'].click()
        },
        deleteItem() {
            if (this.$refs.delete[0].disabled) {
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

        blkSlct() {
            this.bulkSelect = !this.bulkSelect
            this.bulkSelectAll = false
            this.resetInput('bulkList', [])
            this.resetInput(['selectedFile', 'currentFileIndex'])

            if (!this.isBulkSelecting()) {
                this.selectFirst()
            }
        },
        blkSlctAll() {
            // if no items in bulk list
            if (this.bulkList == 0) {
                // if no search query
                if (!this.searchFor) {
                    this.bulkSelectAll = true
                    this.bulkList = this.allFiles.slice(0)
                }

                // if found search items
                if (this.searchFor && this.searchItemsCount) {
                    this.bulkSelectAll = true

                    let list = this.filesList
                    for (let i = list.length - 1; i >= 0; i--) {
                        list[i].click()
                    }
                }
            }

            // if having search + having bulk items < search found items
            else if (this.searchFor && this.bulkItemsCount < this.searchItemsCount) {
                this.resetInput('bulkList', [])
                this.resetInput(['selectedFile', 'currentFileIndex'])

                if (this.bulkSelectAll) {
                    this.bulkSelectAll = false
                } else {
                    this.bulkSelectAll = true

                    let list = this.filesList
                    for (let i = list.length - 1; i >= 0; i--) {
                        list[i].click()
                    }
                }
            }

            // if NO search + having bulk items < all items
            else if (!this.searchFor && this.bulkItemsCount < this.allItemsCount) {
                if (this.bulkSelectAll) {
                    this.bulkSelectAll = false
                    this.resetInput('bulkList', [])
                } else {
                    this.bulkSelectAll = true
                    this.bulkList = this.allFiles.slice(0)
                }

                this.resetInput(['selectedFile', 'currentFileIndex'])
            }

            // otherwise
            else {
                this.bulkSelectAll = false
                this.resetInput('bulkList', [])
                this.resetInput(['selectedFile', 'currentFileIndex'])
            }

            // if we have items in bulk list, select first item
            if (this.bulkItemsCount) {
                this.selectedFile = this.bulkList[0]
            }
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
        },

        /**
         * animation
         *
         * because $nextTick doesnt work correctly
         * with transition-group
         */
        afterEnter() {
            if (this.searchFor) {
                this.updateSearchCount()
            }
        },
        afterLeave() {
            if (this.searchFor) {
                this.updateSearchCount()
            }

            if (!this.allItemsCount) {
                this.resetInput(['selectedFile', 'currentFileIndex'])
            }
        }
    },
    render() {}
}
</script>
