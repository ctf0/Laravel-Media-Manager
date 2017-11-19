<script>
import Form from './mixins/form'
import Ops from './mixins/ops'
import FileFiltration from './mixins/filtration'
import BulkSelect from './mixins/bulk'
import LockFile from './mixins/lock'
import SelectedFile from './mixins/selected'
import Restriction from './mixins/restriction'
import Utilities from './mixins/utils'
import Watchers from './mixins/watch'
import Computed from './mixins/computed'

export default {
    name: 'media-manager',
    mixins: [
        Form,
        Ops,
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
        'inModal',
        'filesRoute',
        'dirsRoute',
        'hideExt',
        'restrictPath',
        'restrictExt',
        'mediaTrans'
    ],
    data() {
        return {
            isLoading: false,
            linkCopied: false,
            toggleInfo: true,
            bulkSelectAll: false,
            bulkSelect: false,
            folderWarning: false,
            uploadToggle: false,

            files: [],
            folders: [],
            directories: [],
            filterdList: [],
            bulkList: [],
            lockedList: [],

            selectedFile: undefined,
            sortBy: undefined,
            currentFilterName: undefined,
            searchItemsCount: undefined,
            searchFor: undefined,
            new_folder_name: undefined,
            new_filename: undefined
        }
    },
    created() {
        if (this.checkForRestrictedPath()) {
            return this.restrictAccess()
        }

        this.getFiles('/')
    },
    mounted() {
        this.fileUpload()
        this.shortCuts()
    },
    updated() {
        this.autoPlay()
    },
    beforeDestroy() {
        $(document).unbind()
    },
    methods: {
        fileUpload() {
            let manager = this

            $('#new-upload').dropzone({
                createImageThumbnails: false,
                parallelUploads: 10,
                uploadMultiple: true,
                forceFallback: false,
                previewsContainer: '#uploadPreview',
                processingmultiple() {
                    $('#uploadProgress').fadeIn()
                },
                totaluploadprogress(uploadProgress) {
                    $('#uploadProgress .progress-bar').css('width', uploadProgress + '%')
                },
                successmultiple(files, res) {
                    res.data.map((item) => {
                        if (item.success) {
                            manager.showNotif(`Successfully Uploaded "${item.message}"`)
                        } else {
                            manager.showNotif(item.message, 'danger')
                        }
                    })

                    manager.getFiles(manager.folders)
                },
                errormultiple(files, res) {
                    this.showNotif(res, 'danger')
                },
                queuecomplete() {
                    manager.$refs.upload.click()
                    $('#uploadProgress').fadeOut(() => {
                        $('#uploadProgress .progress-bar').css('width', 0)
                    })
                }
            })
        },
        shortCuts() {
            $(document).keydown((e) => {

                // when modal isnt visible
                if (!$('.mm-modal').hasClass('is-active')) {
                    // when search is not focused
                    if (!$('.input').is(':focus')) {
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
                                    (this.selectedFileIs('video') || this.selectedFileIs('audio') || this.selectedFileIs('image'))
                                ) {
                                    e.preventDefault()

                                    // play-pause media
                                    if (this.selectedFileIs('video') || this.selectedFileIs('audio')) {
                                        return $('.player')[0].paused
                                            ? $('.player')[0].play()
                                            : $('.player')[0].pause()
                                    }

                                    // "show" image quick view
                                    if (this.selectedFileIs('image')) {
                                        this.noScroll('add')
                                        this.toggleModal('#preview_modal')
                                    }
                                }
                            }
                            // end of when there are files

                            // refresh
                            if (keycode(e) == 'r') {
                                this.$refs.refresh.click()
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

                                this.$refs.bulkSelect.click()
                            }

                            // add all to bulk list
                            if (this.isBulkSelecting() && keycode(e) == 'a') {
                                this.$refs.bulkSelectAll.click()
                            }

                            // delete file
                            if (keycode(e) == 'delete' || keycode(e) == 'd') {
                                this.deleteItem()
                            }

                            // move file
                            if (this.checkForFolders() && keycode(e) == 'm') {
                                this.moveItem()
                            }

                            // lock files
                            if (keycode(e) == 'l') {
                                this.pushToLockedList()
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
                    if (keycode(e) == 'enter') {
                        e.preventDefault()
                        $('.mm-modal.is-active').find('button[type="submit"]').trigger('click')
                    }

                    if (this.lightBoxIsActive()) {
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
            })
        },

        /**
         * autoplay media
         *
         * last item will keep repeating
         * unless we added the constrain "!last"
         * but now it wont play
         */
        autoPlay() {
            if (this.filterNameIs('audio') || this.filterNameIs('video')) {
                $('.player').bind('ended', () => {
                    // nav to next
                    this.goToNext()

                    let last = $('#files li:last-of-type').find('div.selected').length

                    // play navigated to
                    if (!last) {
                        this.$nextTick(() => {
                            $('.player')[0].play()
                        })
                    }
                })
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

            if (this.allItemsCount == undefined) {
                this.clearSelected()
            }
        }
    },
    render() {}
}
</script>
