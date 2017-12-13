<script>
import Form from './mixins/form'
import FileFiltration from './mixins/filtration'
import BulkSelect from './mixins/bulk'
import LockFile from './mixins/lock'
import SelectedFile from './mixins/selected'
import Restriction from './mixins/restriction'
import Utilities from './mixins/utils'
import Watchers from './mixins/watch'
import Computed from './mixins/computed'

import Bounty from 'vue-bounty'

export default {
    components: {Bounty},
    name: 'media-manager',
    mixins: [
        Form,
        FileFiltration,
        BulkSelect,
        LockFile,
        SelectedFile,
        Restriction,
        Utilities,
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
        'restrictPath',
        'uploadPanelImgList',
        'hideExt',
        'hidePath'
    ],
    data() {
        return {
            isLoading: false,
            no_files: false,
            loading_files: false,
            no_search: false,
            ajax_error: false,
            toggleInfo: true,
            uploadToggle: false,
            uploadStart: false,
            uploadProgress: 0,

            linkCopied: false,
            bulkSelectAll: false,
            bulkSelect: false,
            folderWarning: false,
            checkForFolders: false,
            randomNames: false,
            toolBar: true,

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
            new_folder_name: null,
            new_filename: null,
            active_modal: null,
            navDirection: ''
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
            let ls = this.$ls.get('mediamanager')

            if (ls) {
                this.randomNames = ls.randomNames ? ls.randomNames : false
                this.folders = ls.folders ? ls.folders : []
                this.toolBar = ls.toolBar ? ls.toolBar : true
                this.selectedFile = ls.selectedFileName ? ls.selectedFileName : null
            }
        },

        fileUpload() {
            let manager = this
            let counter = 0
            let progress = 0
            let increaseBy = 0

            new Dropzone('#new-upload', {
                createImageThumbnails: false,
                parallelUploads: 10,
                uploadMultiple: true,
                forceFallback: false,
                ignoreHiddenFiles: true,
                timeout: 3600000,
                previewsContainer: '#uploadPreview',
                addedfile() {
                    manager.uploadStart = true
                    counter++
                    increaseBy = 100 / counter
                },
                sending() {
                    progress += increaseBy
                    return manager.uploadProgress = `${progress}%`
                },
                successmultiple(files, res) {
                    res.data.map((item) => {
                        item.success
                            ? manager.showNotif(`${manager.trans('upload_success')} "${item.message}"`)
                            : manager.showNotif(item.message, 'danger')
                    })

                    res.data.length
                        ? manager.getFiles(manager.folders, null, res.data[res.data.length - 1].message)
                        : manager.getFiles(manager.folders)
                },
                errormultiple(files, res) {
                    manager.showNotif(res, 'danger')
                },
                queuecomplete() {
                    manager.uploadStart = false
                    manager.toggleUploadPanel()
                    manager.uploadProgress = 0
                }
            })
        },
        /* end of upload */

        shortCuts(e) {
            // when modal isnt visible
            if (!this.active_modal) {
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
                        }
                        // end of when there are files

                        // refresh
                        if (keycode(e) == 'r') {
                            this.refresh()
                        }

                        // file upload
                        if (keycode(e) == 'u') {
                            this.toggleUploadPanel()
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

                        // add all to bulk list
                        if (this.isBulkSelecting() && keycode(e) == 'a') {
                            if (this.$refs.bulkSelectAll) {
                                this.$refs.bulkSelectAll.click()
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
        },
        /* end of short cuts */

        refresh() {
            this.getFiles(this.folders, null, this.selectedFile.name)
        },
        deleteItem() {
            if (this.$refs.delete[0].disabled) {
                return
            }

            if (!this.isBulkSelecting() && this.selectedFile) {
                this.selectedFileIs('folder')
                    ? this.folderWarning = true
                    : this.folderWarning = false

                this.$refs.confirm_delete.innerText = this.selectedFile.name
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
        moveItem() {
            if (this.$refs.move[0].disabled) {
                return
            }

            this.toggleModal('move_file_modal')
        },
        renameItem() {
            this.toggleModal('rename_file_modal')
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
