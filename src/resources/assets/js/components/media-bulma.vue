<script>
/* external classes */
// is-warning
// is-danger
// field
// has-addons
// icon
// fa-plus / fa-minus
// fa-refresh / fa-spin
// fa fa-angle-double-right
// fa fa-angle-double-left

import Form from './mixins/form'
import FileFiltration from './mixins/filtration'
import BulkSelect from './mixins/bulk'
import LockFile from './mixins/lock'
import SelectedFile from './mixins/selected'
import Restriction from './mixins/restriction'
import Utilities from './mixins/utils'
import Watchers from './mixins/watch'

export default {
    name: 'media-manager',
    mixins: [
        Form,
        FileFiltration,
        BulkSelect,
        LockFile,
        SelectedFile,
        Restriction,
        Utilities,
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
            is_loading: false,
            linkCopied: false,
            files: [],
            folders: [],
            directories: [],
            filterdList: [],
            bulkList: [],
            lockedList: [],
            selectedFile: undefined,
            showBy: undefined,
            currentFilterName: undefined,
            searchItemsCount: undefined,
            searchFor: undefined,
            new_folder_name: undefined,
            new_filename: undefined
        }
    },
    computed: {
        allFiles() {
            if (this.filterItemsCount) {
                return this.filterdList
            }

            return this.files.items
        },
        filterItemsCount() {
            if (typeof this.filterdList !== 'undefined' && this.filterdList.length > 0) {
                return this.filterdList.length
            }
        },
        allItemsCount() {
            if (typeof this.allFiles !== 'undefined' && this.allFiles.length > 0) {
                return this.allFiles.length
            }
        },
        bulkItemsCount() {
            if (typeof this.bulkList !== 'undefined' && this.bulkList.length > 0) {
                return this.bulkList.length
            }
        },
        bulkListFilter() {
            let list = this.bulkList

            if (this.lockedList.length) {
                list = this.bulkList.filter((x) => {
                    return this.lockedList.indexOf(x) < 0
                })
            }

            if (list.length > 0) {
                $('#delete').removeAttr('disabled')
            }

            return list
        }
    },
    created() {
        if (this.checkForRestrictedPath()) {
            return this.restrictAccess()
        }

        this.getFiles('/')
    },
    mounted() {
        this.initManager()
    },
    updated() {
        this.autoPlay()
    },
    beforeDestroy() {
        $(document).unbind()
    },
    methods: {
        // init
        initManager() {
            let manager = this

            //********** File Upload **********//
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
                    $('#upload').trigger('click')
                    $('#uploadProgress').fadeOut(() => {
                        $('#uploadProgress .progress-bar').css('width', 0)
                    })
                }
            })

            //********** Key Press **********//

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
                                $('#refresh').trigger('click')
                            }

                            // file upload
                            if (keycode(e) == 'u') {
                                $('#upload').trigger('click')
                            }
                        }
                        /* end of no bulk selection */

                        // with or without bulk selection
                        if (this.allItemsCount) {
                            // bulk select
                            if (keycode(e) == 'b') {
                                $('#blk_slct').trigger('click')
                            }

                            // add all to bulk list
                            if (this.isBulkSelecting() && keycode(e) == 'a') {
                                $('#blk_slct_all').trigger('click')
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
                            $('.toggle').trigger('click')
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

            //********** Toolbar Buttons **********//

            // bulk select
            $('#blk_slct').click(function() {
                $(this).toggleClass('is-danger')
                $(this).closest('.field').toggleClass('has-addons')

                // reset when toggled off
                if (!manager.isBulkSelecting()) {
                    $('#upload, #new_folder, #refresh, #rename').parent().show()

                    if ($('#blk_slct_all').hasClass('is-warning')) {
                        $('#blk_slct_all').trigger('click')
                    }

                    $('#blk_slct_all').hide()

                    manager.clearSelected()
                    manager.resetInput('bulkList', [])
                    return manager.selectFirst()
                }

                $('#upload, #new_folder, #refresh, #rename').parent().hide()
                $('#blk_slct_all').fadeIn()
                manager.clearSelected()
            })

            // select all files
            $('#blk_slct_all').click(function() {
                let btn = $(this)

                // if no items in bulk list
                if (manager.bulkList == 0) {
                    // if no search query
                    if (!manager.searchFor) {
                        btn.addClass('is-warning')
                        manager.bulkList = manager.allFiles.slice(0)
                    }

                    // if found search items
                    if (manager.searchItemsCount) {
                        btn.addClass('is-warning')
                        $('#files li').each(function() {
                            btn.trigger('click')
                        })
                    }
                }

                // if having search + having bulk items < search found items
                else if (manager.searchFor && manager.bulkItemsCount < manager.searchItemsCount) {
                    manager.resetInput('bulkList', [])
                    manager.clearSelected()

                    if (btn.hasClass('is-warning')) {
                        btn.removeClass('is-warning')
                    } else {
                        btn.addClass('is-warning')
                        $('#files li').each(function() {
                            btn.trigger('click')
                        })
                    }
                }

                // if NO search + having bulk items < all items
                else if (!manager.searchFor && manager.bulkItemsCount < manager.allItemsCount) {
                    if (btn.hasClass('is-warning')) {
                        btn.removeClass('is-warning')
                        manager.resetInput('bulkList', [])
                    } else {
                        btn.addClass('is-warning')
                        manager.bulkList = manager.allFiles.slice(0)
                    }

                    manager.clearSelected()
                }

                // otherwise
                else {
                    btn.removeClass('is-warning')
                    manager.resetInput('bulkList', [])
                    manager.clearSelected()
                }

                // if we have items in bulk list, select first item
                if (manager.bulkItemsCount) {
                    manager.selectedFile = manager.bulkList[0]
                }

                // toggle styling
                let toggle_text = btn.find('span').not('.icon')

                if (btn.hasClass('is-warning')) {
                    btn.find('.fa').removeClass('fa-plus').addClass('fa-minus')
                    toggle_text.text(manager.trans('select_non'))
                } else {
                    btn.find('.fa').removeClass('fa-minus').addClass('fa-plus')
                    toggle_text.text(manager.trans('select_all'))
                }
            })

            // upload
            $('#upload').click(() => {
                $('#dz').fadeToggle('fast')
            })

            // delete
            $('#delete').click(() => {
                if (!manager.isBulkSelecting()) {
                    if (this.selectedFileIs('folder')) {
                        $('.folder_warning').show()
                    } else {
                        $('.folder_warning').hide()
                    }

                    $('#confirm_delete').text(this.selectedFile.name)
                }

                if (this.bulkItemsCount) {
                    this.bulkListFilter.some((item) => {
                        if (this.fileTypeIs(item, 'folder')) {
                            return $('.folder_warning').show()
                        }

                        $('.folder_warning').hide()
                    })
                }
            })
        },

        /**
         * keyboard navigation
         */
        navigation(e) {
            // go to prev image
            if (keycode(e) == 'left' || keycode(e) == 'up') {
                e.preventDefault()
                this.goToPrev()
            }

            // go to next image
            if (keycode(e) == 'right' || keycode(e) == 'down') {
                e.preventDefault()
                this.goToNext()
            }

            // go to last item
            if (keycode(e) == 'end') {
                e.preventDefault()

                let newSelected = this.allItemsCount - 1
                let cur = $('div[data-index="' + newSelected + '"]')
                this.scrollToFile(cur)
            }

            // go to first item
            if (keycode(e) == 'home') {
                e.preventDefault()
                this.scrollToFile()
            }

            if (this.lightBoxIsActive() && !this.selectedFileIs('image')) {
                this.toggleModal()
            }
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
        }
    },
    render() {}
}
</script>
