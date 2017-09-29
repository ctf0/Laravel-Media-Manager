<script>
/* external classes */
// is-warning
// is-danger
// field
// has-addons
// fa-plus
// fa-minus
// fa fa-angle-double-right
// fa fa-angle-double-left

import Form from './mixins/methods/form'
import FileFiltration from './mixins/methods/filtration'
import BulkSelect from './mixins/methods/bulk'
import SelectedFile from './mixins/methods/selected'
import Utilities from './mixins/methods/utils'
import Watchers from './mixins/watch'

export default {
    name: 'media-manager',
    mixins: [Form, FileFiltration, BulkSelect, SelectedFile, Utilities, Watchers],
    props: ['filesRoute', 'dirsRoute', 'hideExt'],
    data() {
        return {
            files: [],
            folders: [],
            directories: [],
            filterdList: [],
            bulkList: [],
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
            if (typeof this.filterdList !== 'undefined' && this.filterdList.length > 0) {
                return this.filterdList
            }

            return this.files.items
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
        }
    },
    created() {
        this.getFiles('/')
    },
    mounted() {
        this.$tippy.forceUpdateHtml()
        this.initManager()
    },
    updated() {

        // autoplay media
        // last item will keep repeating unless we added the constrain "!last" below
        // but now it wont play

        if (this.filterNameIs('audio') || this.filterNameIs('video')) {
            $('.player').bind('ended', () => {
                // nav to next
                $('#files li .selected').trigger($.Event('keydown', {keyCode: keycode('right')}))

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
    methods: {
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
                            manager.showNotif({
                                title: 'Success',
                                body: `Successfully Uploaded "${item.message}"`,
                                type: 'success',
                                duration: 5
                            })
                        } else {
                            manager.showNotif({
                                title: 'Error',
                                body: item.message,
                                type: 'danger'
                            })
                        }
                    })

                    manager.getFiles(manager.folders)
                },
                errormultiple(files, res) {
                    this.showNotif({
                        title: 'Error',
                        body: res,
                        type: 'danger'
                    })
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

                let curSelected = $('#files li .selected')
                let curSelectedIndex = parseInt(curSelected.data('index'))

                // when modal isnt visible
                if (!$('.modal').hasClass('is-active')) {
                    // when search is not focused
                    if (!$('.input').is(':focus')) {
                        // lightbox is not active
                        if (!this.lightBoxIsActive()) {
                            // when no bulk selecting
                            if (!this.isBulkSelecting()) {

                                let cur = ''
                                let newSelected = ''
                                let index = ''

                                if ((keycode(e) == 'left' || keycode(e) == 'up') && curSelectedIndex !== 0) {
                                    e.preventDefault()

                                    newSelected = curSelectedIndex - 1
                                    cur = $('div[data-index="' + newSelected + '"]')
                                    this.scrollToFile(cur)
                                }

                                if ((keycode(e) == 'right' || keycode(e) == 'down') && curSelectedIndex < this.allItemsCount - 1) {
                                    e.preventDefault()

                                    newSelected = curSelectedIndex + 1
                                    cur = $('div[data-index="' + newSelected + '"]')
                                    this.scrollToFile(cur)
                                }

                                // open folder
                                if (keycode(e) == 'enter') {
                                    this.openFolder(this.selectedFile)
                                }

                                // go up a dir
                                if (keycode(e) == 'backspace') {
                                    index = parseInt(this.folders.length) - 1

                                    if (index < 0) {
                                        return false
                                    }

                                    this.goToFolder(index)
                                }

                                // go to first / last item
                                if (this.allItemsCount) {
                                    if (keycode(e) == 'home') {
                                        e.preventDefault()

                                        this.scrollToFile()
                                    }

                                    if (keycode(e) == 'end') {
                                        e.preventDefault()

                                        index = this.allItemsCount - 1
                                        cur = $('div[data-index="' + index + '"]')
                                        this.scrollToFile(cur)
                                    }
                                }

                                // file upload
                                if (keycode(e) == 'u') {
                                    $('#upload').trigger('click')
                                }

                                // play-pause for media
                                if (keycode(e) == 'space' && e.target == document.body) {
                                    e.preventDefault()

                                    if (this.selectedFileIs('video') || this.selectedFileIs('audio')) {
                                        return $('.player')[0].paused
                                            ? $('.player')[0].play()
                                            : $('.player')[0].pause()
                                    }

                                    // "show" image quick view
                                    if (this.selectedFileIs('image')) {
                                        this.noScroll('add')
                                        this.toggleModal('#img_modal')
                                    }
                                }
                            }
                            /* end of no bulk selection */

                            // when there are files
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
                                    $('#delete').trigger('click')
                                }

                                // refresh
                                if (keycode(e) == 'r') {
                                    $('#refresh').trigger('click')
                                }

                                // move file
                                if (this.checkForFolders() && keycode(e) == 'm') {
                                    $('#move').trigger('click')
                                }
                            }
                            /* end of there are files */

                            // toggle file details sidebar
                            if (keycode(e) == 't') {
                                $('.toggle').trigger('click')
                            }
                        }
                        /* end of no lightbox is active */
                    }
                    /* end of search is not focused */
                }
                /* end of modal isnt visible */

                // when modal is visible
                else {
                    if (keycode(e) == 'enter') {
                        e.preventDefault()
                        $('.modal.is-active').find('.submit').trigger('click')
                    }

                    if (this.lightBoxIsActive() && keycode(e) == 'space') {
                        e.preventDefault()
                        this.toggleModal()
                    }

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
                $('#upload, #new_folder, #refresh, #rename').parent().hide()
                $(this).closest('.field').toggleClass('has-addons')
                $('#blk_slct_all').fadeIn()

                // reset when toggled off
                if (!manager.isBulkSelecting()) {
                    $('#upload, #new_folder, #refresh, #rename').parent().show()

                    if ($('#blk_slct_all').hasClass('is-warning')) {
                        $('#blk_slct_all').trigger('click')
                    }

                    $('#blk_slct_all').hide()

                    $('li.bulk-selected').removeClass('bulk-selected')
                    manager.bulkList = []
                    manager.selectFirst()
                }

                manager.clearSelected()
            })

            // select all files
            $('#blk_slct_all').click(function() {
                // if no items in bulk list
                if (manager.bulkList == 0) {
                    // if no search query
                    if (!manager.searchFor) {
                        $(this).addClass('is-warning')
                        manager.bulkList = manager.allFiles.slice(0)
                    }

                    // if found search items
                    if (manager.searchItemsCount) {
                        $(this).addClass('is-warning')
                        $('#files li').each(function() {
                            $(this).trigger('click')
                        })
                    }
                }

                // if having search + having bulk items < search found items
                else if (manager.searchFor && manager.bulkItemsCount < manager.searchItemsCount) {
                    manager.bulkList = []
                    manager.clearSelected()

                    if ($(this).hasClass('is-warning')) {
                        $(this).removeClass('is-warning')
                    } else {
                        $(this).addClass('is-warning')
                        $('#files li').each(function() {
                            $(this).trigger('click')
                        })
                    }
                }

                // if NO search + having bulk items < all items
                else if (!manager.searchFor && manager.bulkItemsCount < manager.allItemsCount) {
                    if ($(this).hasClass('is-warning')) {
                        $(this).removeClass('is-warning')
                        manager.bulkList = []
                    } else {
                        $(this).addClass('is-warning')
                        manager.bulkList = manager.allFiles.slice(0)
                    }

                    manager.clearSelected()
                }

                // otherwise
                else {
                    $(this).removeClass('is-warning')
                    manager.bulkList = []
                    manager.clearSelected()
                }

                // if we have items in bulk list, select first item
                if (manager.bulkItemsCount) {
                    manager.selectedFile = manager.bulkList[0]
                }

                // toggle styling
                let toggle_text = $(this).find('span').not('.icon')

                if ($(this).hasClass('is-warning')) {
                    $(this).find('.fa').removeClass('fa-plus').addClass('fa-minus')
                    toggle_text.text('Select Non')
                } else {
                    $(this).find('.fa').removeClass('fa-minus').addClass('fa-plus')
                    toggle_text.text('Select All')
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
                    $('.folder_warning').hide()

                    this.bulkList.some((item) => {
                        if (item.type.includes('folder')) {
                            $('.folder_warning').show()
                        }
                    })
                }
            })
        }
    },
    render() {}
}
</script>
