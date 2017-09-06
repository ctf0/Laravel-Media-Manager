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

export default {
    name: 'media-manager',
    data() {
        return {
            files: [],
            folders: [],
            directories: [],
            filterdList: [],
            bulkList: [],
            showBy: null,
            currentFilterName: undefined,
            selectedFile: undefined,
            searchItemsCount: undefined,
            searchFor: undefined
        }
    },
    computed: {
        allFiles() {
            if (typeof this.filterdList !== 'undefined' && this.filterdList.length > 0) {
                return this.filterdList
            } else {
                return this.files.items
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
        }
    },
    mounted() {
        this.initManager()
    },
    methods: {
        /*                Render                */
        initManager() {
            let manager = this

            this.getFiles('/')

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

                let curSelected = parseInt($('#files li .selected').data('index'))

                // when modal isnt visible
                if (!$('#new_folder_modal').is(':visible') &&
                    !$('#move_file_modal').is(':visible') &&
                    !$('#rename_file_modal').is(':visible') &&
                    !$('#confirm_delete_modal').is(':visible')) {

                    // when search is not focused
                    if (!$('.input').is(':focus')) {

                        // when no bulk selecting & no light box is active
                        if (!this.isBulkSelecting() && !this.lightBoxIsActive()) {

                            let cur = ''
                            let newSelected = ''

                            if ((keycode(e) == 'left' || keycode(e) == 'up') && curSelected !== 0) {
                                newSelected = curSelected - 1
                                cur = $('div[data-index="' + newSelected + '"]')
                                this.scrollToFile(cur)
                            }

                            if ((keycode(e) == 'right' || keycode(e) == 'down') && curSelected < this.allItemsCount - 1) {
                                newSelected = curSelected + 1
                                cur = $('div[data-index="' + newSelected + '"]')
                                this.scrollToFile(cur)
                            }

                            // open folder
                            if (keycode(e) == 'enter') {

                                if (!this.selectedFileIs('folder')) {
                                    return false
                                }

                                this.currentFilterName = undefined
                                this.folders.push(this.selectedFile.name)
                                this.getFiles(this.folders)
                            }

                            // go up a dir
                            if (keycode(e) == 'backspace') {

                                index = parseInt(this.folders.length) - 1

                                if (index < 0) {
                                    return false
                                }

                                if (index === 0) {
                                    this.folders = []
                                    this.getFiles(this.folders)
                                } else {
                                    this.folders = this.folders.splice(0, index)
                                    this.getFiles(this.folders)
                                }

                                this.currentFilterName = undefined
                            }

                            // go to first / last item
                            if (this.allItemsCount) {

                                if (keycode(e) == 'home') {
                                    this.scrollToFile()
                                }

                                if (keycode(e) == 'end') {
                                    let index = this.allItemsCount - 1
                                    cur = $('div[data-index="' + index + '"]')
                                    this.scrollToFile(cur)
                                }
                            }

                            // file upload
                            if (keycode(e) == 'u') {
                                $('#upload').trigger('click')
                            }
                        }

                        // quick view for images / play audio or video
                        if (!this.isBulkSelecting()) {

                            if (keycode(e) == 'space' && e.target == document.body) {
                                // prevent body from scrolling
                                e.preventDefault()

                                // play audio/video
                                if (this.selectedFileIs('video') || this.selectedFileIs('audio')) {
                                    return $('.player')[0].paused
                                        ? $('.player')[0].play()
                                        : $('.player')[0].pause()
                                }

                                // quick view image
                                if (this.selectedFileIs('image')) {
                                    if (this.lightBoxIsActive()) {
                                        $('#vue-lightboxOverlay').trigger('click')
                                    } else {
                                        $('.quickView').trigger('click')
                                    }
                                }
                            }

                            // quick view image "esc"
                            if (keycode(e) == 'esc' && this.selectedFileIs('image') && this.lightBoxIsActive()) {
                                $('#vue-lightboxOverlay').trigger('click')
                                e.preventDefault()
                            }
                        }
                        /* end of no bulk selection */

                        // when there are files
                        if (this.allItemsCount) {

                            // when lightbox is not active
                            if (!this.lightBoxIsActive()) {
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
                                if (this.checkForFolders()) {
                                    if (keycode(e) == 'm') {
                                        $('#move').trigger('click')
                                    }
                                }
                            }
                            /* end when lightbox is not active */
                        }
                        /* end of there are files */

                        // toggle file details box
                        if (keycode(e) == 't' && !this.lightBoxIsActive()) {
                            $('.toggle').trigger('click')
                        }
                    }
                    /* end of search is not focused */
                }
                /* end of modal isnt visible */

                // when modal is visible
                if (keycode(e) == 'enter') {
                    if ($('#confirm_delete_modal').is(':visible')) {
                        $('#confirm_delete').trigger('click')
                    }

                    if ($('#rename_file_modal').is(':visible')) {
                        $('#rename_btn').trigger('click')
                    }

                    if ($('#new_folder_modal').is(':visible')) {
                        $('#new_folder_submit').trigger('click')
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

            // refresh
            $('#refresh').click(() => {
                this.getFiles(this.folders)
            })

            // upload
            $('#upload').click(() => {
                $('#new-upload').fadeToggle('fast')
            })

            // new folder
            $('#new_folder').click(() => {
                $('#new_folder_modal').modal('show')
            })

            $('#new_folder_modal').on('shown.bs.modal', () => {
                $('#new_folder_name').focus()
            })

            $('#new_folder_submit').click(() => {

                $.post(route('media.new_folder'), {
                    current_path: this.files.path,
                    new_folder_name: $('#new_folder_name').val()
                }, (data) => {
                    if (data.success) {
                        this.showNotif({
                            title: 'Success',
                            body: `Successfully Created "${data.new_folder_name}" at "${data.full_path}"`,
                            type: 'success',
                            duration: 5
                        })
                        this.getFiles(this.folders)
                    } else {
                        this.showNotif({
                            title: 'Error',
                            body: data.message,
                            type: 'danger'
                        })
                    }

                    $('#new_folder_name').val('')
                    $('#new_folder_modal').modal('hide')
                })
            })

            // delete
            $('#delete').click(() => {

                if (!manager.isBulkSelecting()) {
                    if (this.selectedFileIs('folder')) {
                        $('.folder_warning').show()
                    } else {
                        $('.folder_warning').hide()
                    }
                    $('.confirm_delete').text(this.selectedFile.name)
                }

                if (this.bulkItemsCount) {
                    $('.folder_warning').hide()
                    this.bulkList.some((item) => {
                        if (item.type.includes('folder')) {
                            $('.folder_warning').show()
                        }
                    })
                }

                $('#confirm_delete_modal').modal('show')
            })

            $('#confirm_delete').click(() => {

                if (this.bulkItemsCount) {
                    this.delete_file(this.bulkList)
                    $('#blk_slct').trigger('click')
                } else {
                    this.delete_file([this.selectedFile])
                }
            })

            // move
            $('#move').click(() => {
                $('#move_file_modal').modal('show')
            })

            $('#move_btn').click(() => {

                if (this.bulkItemsCount) {
                    this.move_file(this.bulkList)
                    $('#blk_slct').trigger('click')
                } else {
                    this.move_file([this.selectedFile])
                }
            })

            // rename
            $('#rename').click(() => {
                $('#rename_file_modal').modal('show')
            })

            $('#rename_file_modal').on('shown.bs.modal', () => {
                $('#new_filename').focus()
            })

            $('#rename_btn').click(() => {

                let filename = this.selectedFile.name
                let ext = filename.substring(filename.lastIndexOf('.') + 1)
                let new_filename = $('#new_filename').val() + `.${ext}`

                $.post(route('media.rename_file'), {
                    folder_location: this.folders,
                    filename: filename,
                    new_filename: new_filename
                }, (data) => {

                    if (data.success) {
                        this.showNotif({
                            title: 'Success',
                            body: `Successfully Renamed "${filename}" to "${data.new_filename}"`,
                            type: 'success',
                            duration: 5
                        })

                        this.updateItemName(this.selectedFile, filename, data.new_filename)

                        if (this.selectedFileIs('folder')) {
                            this.updateDirsList()
                        }
                    } else {
                        this.showNotif({
                            title: 'Error',
                            body: data.message,
                            type: 'danger'
                        })
                    }

                    $('#rename_file_modal').modal('hide')
                })
            })
        },

        /*                Main                */
        getFiles(folders) {
            $('#file_loader').show()
            this.searchFor = ''
            this.showFilesOfType('all')
            this.showBy = null

            let folder_location = ''

            if (folders != '/') {
                folder_location = '/' + folders.join('/')
            } else {
                folder_location = '/'
            }

            // files list
            $.post(route('media.files'), {
                folder: folder_location
            }, (res) => {

                this.files = res
                $('#file_loader').hide()
                this.selectFirst()
                $('#right').fadeIn()

                this.allFiles.map((e) => {
                    if (typeof e.size !== 'undefined') {
                        e.size = this.bytesToSize(e.size)
                    }
                })
            })

            // dirs list
            this.updateDirsList()
        },
        bytesToSize(bytes) {
            if (bytes === 0) {
                return '0 Bytes'
            }

            let sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
            let i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))

            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i]
        },
        delete_file(files) {

            $.post(route('media.delete_file_folder'), {
                folder_location: this.folders,
                deleted_files: files
            }, (res) => {

                res.data.map((item) => {
                    if (item.success) {
                        this.showNotif({
                            title: 'Success',
                            body: `Successfully Deleted "${item.name}"`,
                            type: 'warning',
                            duration: 5
                        })
                        this.removeFromLists(item.name)
                    } else {
                        this.showNotif({
                            title: 'Error',
                            body: item.message,
                            type: 'danger'
                        })
                    }
                })

                $('#confirm_delete_modal').modal('hide')
                this.updateFoundCount(files.length)
                this.selectFirst()
            })
        },
        move_file(files) {

            let destination = $('#move_folder_dropdown').val()

            $.post(route('media.move_file'), {
                folder_location: this.folders,
                destination: destination,
                moved_files: files
            }, (res) => {

                res.data.map((item) => {
                    if (item.success) {
                        this.showNotif({
                            title: 'Success',
                            body: `Successfully moved "${item.name}" to "${destination}"`,
                            type: 'success',
                            duration: 5
                        })

                        this.removeFromLists(item.name)
                        this.updateFolderCount(destination, 1)

                        // update dirs list after move
                        if (item.type.includes('folder')) {
                            this.updateDirsList()
                        }
                    } else {
                        this.showNotif({
                            title: 'Error',
                            body: item.message,
                            type: 'danger'
                        })
                    }
                })

                // update folder count when folder is moved into another
                files.map((e) => {
                    if (e.items && e.items > 0) {
                        this.updateFolderCount(destination, e.items)
                    }
                })

                $('#move_file_modal').modal('hide')
                this.updateFoundCount(files.length)
                this.selectFirst()
            })
        },

        /*                Bulk                */
        isBulkSelecting() {
            return $('#blk_slct').hasClass('is-danger')
        },
        IsInBulkList(file) {
            return this.bulkList.includes(file)
        },
        pushtoBulkList(file) {
            if (!this.bulkItemsCount) {
                return this.bulkList.push(file)
            }

            if (!this.bulkList.includes(file)) {
                return this.bulkList.push(file)
            } else {
                this.bulkList.splice(this.bulkList.indexOf(file), 1)

                // select prev item
                if (this.bulkItemsCount) {
                    this.selectedFile = this.bulkList[this.bulkItemsCount - 1]
                } else {
                    // clear slection
                    this.clearSelected()
                }
            }
        },

        /*                Selected                */
        selectFirst() {
            this.$nextTick(() => {
                let file = $('div[data-index="0"]')
                if (file.length > 0) {
                    file.trigger('click')
                }
            })
        },
        setSelected(file) {
            this.clearSelected()
            $('div[data-folder="' + file.name + '"]').addClass('selected')
            this.selectedFile = file

            if (this.isBulkSelecting()) {
                this.pushtoBulkList(file)
            }
        },
        clearSelected() {
            this.selectedFile = undefined
            $('#files li .selected').removeClass('selected')
        },
        openFolder(file) {
            if (!this.isBulkSelecting()) {

                if (!this.fileTypeIs(file, 'folder')) {
                    return false
                }

                this.folders.push(file.name)
                this.getFiles(this.folders)
            }

            this.currentFilterName = undefined
        },
        goToFolder(index) {
            if (!this.isBulkSelecting()) {
                this.folders = this.folders.splice(0, index)
                this.getFiles(this.folders)
            }
        },
        scrollToFile(file) {

            if (!file) {
                file = $('div[data-index="0"]')
            }

            $(file).trigger('click')

            $('#left').scrollTo($(file), 0, {
                margin: true,
                offset: -8
            })
        },

        /*                Filtration                */
        btnFilter(val) {
            if (val == 'all') {
                return this.filterdList.length
            }

            return this.files.items.some((item) => {
                return this.fileTypeIs(item, val)
            })
        },
        selectedFileIs(val) {
            if (typeof this.selectedFile !== 'undefined') {
                return this.fileTypeIs(this.selectedFile, val)
            }
        },
        filterNameIs(val) {
            return this.currentFilterName == val
        },
        fileTypeIs(item, val) {
            if (val == 'text') {
                if (!item.type.includes('folder') &&
                    !item.type.includes('image') &&
                    !item.type.includes('video') &&
                    !item.type.includes('audio')) {
                    return true
                }
            } else {
                return item.type.includes(val)
            }
        },
        showFilesOfType(val) {
            if (this.currentFilterName == val) {
                return false
            }

            if (val == 'all') {
                this.filterdList = []
                this.currentFilterName = undefined
            } else {
                this.filterdList = this.files.items.filter((item) => {
                    return this.fileTypeIs(item, val)
                })

                this.currentFilterName = val
            }

            if (!this.isBulkSelecting()) {
                this.clearSelected()
                this.selectFirst()
            }

            if (this.searchFor) {
                this.updateSearchCount()
            }
        },
        filterDir(dir) {
            // dont show dirs that have similarity with selected item(s)
            if (this.bulkItemsCount) {

                if (this.bulkList.filter((e) => dir.match(`(/?)${e.name}(/?)`)).length > 0) {
                    return false
                } else {
                    return true
                }

            } else {
                return this.selectedFile && !dir.includes(this.selectedFile.name)
            }
        },
        checkForFolders() {
            if ($('#move_folder_dropdown').val() !== null) {
                return true
            } else {
                return false
            }
        },

        /*                Operations                */
        removeFromLists(name) {
            if (this.filterdList.length) {
                let list = this.filterdList

                list.map((e) => {
                    if (e.name.includes(name)) {
                        list.splice(list.indexOf(e), 1)
                    }
                })
            }

            if (this.directories.length) {
                let list = this.directories

                list.map((e) => {
                    if (e.includes(name)) {
                        list.splice(list.indexOf(e), 1)
                    }
                })
            }

            this.files.items.map((e) => {
                if (e.name.includes(name)) {
                    let list = this.files.items

                    list.splice(list.indexOf(e), 1)
                }
            })

            this.clearSelected()
        },
        updateFolderCount(destination, count) {
            if (destination !== '../') {

                if (destination.includes('/')) {
                    destination = destination.split('/').shift()
                }

                if (this.filterdList.length) {
                    this.filterdList.map((e) => {
                        if (e.name.includes(destination)) {
                            e.items += parseInt(count)
                        }
                    })
                }

                this.files.items.map((e) => {
                    if (e.name.includes(destination)) {
                        e.items += parseInt(count)
                    }
                })
            }
        },
        updateItemName(item, oldName, newName) {
            // update the main files list
            let filesIndex = this.files.items[this.files.items.indexOf(item)]
            filesIndex.name = newName
            filesIndex.path = filesIndex.path.replace(oldName, newName)

            // if found in the filterd list, then update it aswell
            if (this.filterdList.includes(item)) {
                let filterIndex = this.filterdList[this.filterdList.indexOf(item)]
                filterIndex.name = newName
                filesIndex.path = filterIndex.path.replace(oldName, newName)
            }
        },
        updateSearchCount() {
            this.$nextTick(() => {
                this.searchItemsCount = parseInt($('#files li').length)

                if (this.searchItemsCount == 0) {
                    $('#no_files').fadeIn()
                } else {
                    $('#no_files').hide()
                }
            })
        },
        updateFoundCount(count) {
            if (this.searchFor) {
                this.searchItemsCount = parseInt(this.searchItemsCount - count)
            }
        },
        updateDirsList() {
            $.post(route('media.directories'), {
                folder_location: this.folders
            }, (data) => {
                this.directories = data
            })
        },

        /*                Utils                */
        lastItem(item, list) {
            return item == list[list.length - 1]
        },
        toggleInfo() {
            $('#right').fadeToggle()
            let span = $('.toggle').find('span').not('.icon')
            span.text(span.text() == 'Close' ? 'Open' : 'Close')
            $('.toggle').find('.fa').toggleClass('fa fa-angle-double-right').toggleClass('fa fa-angle-double-left')
        },
        lightBoxIsActive() {
            return $('#vue-lightboxOverlay').is(':visible')
        },
        fileName(name) {
            return name.replace(/(.[^.]*)$/, '')
        },
        showNotif(data) {
            EventHub.fire('showNotif', {
                title: data.title,
                body: data.body,
                type: data.type,
                duration: data.duration || null
            })
        }
    },
    watch: {
        allFiles(newVal) {
            if (newVal.length < 1) {
                $('#no_files').fadeIn()
            } else {
                $('#no_files').hide()
            }
        },
        bulkList(val) {
            if (val) {
                // hide move button when all folders are selected
                this.$nextTick(() => {
                    if (!this.checkForFolders()) {
                        $('#move').attr('disabled', true)
                    }
                })
            }

            if (val == 0 && this.isBulkSelecting()) {
                let toggle_text = $('#blk_slct_all').find('span').not('.icon')
                $('#blk_slct_all').removeClass('is-warning')
                $('#blk_slct_all').find('.fa').removeClass('fa-minus').addClass('fa-plus')
                toggle_text.text('Select All')
            }
        },
        selectedFile(val) {
            if (!val) {
                $('#move').attr('disabled', true)
                $('#rename').attr('disabled', true)
                $('#delete').attr('disabled', true)
            } else {
                // hide move button when there is only one folder and its selected
                this.$nextTick(() => {
                    if (!this.checkForFolders()) {
                        $('#move').attr('disabled', true)
                    }
                })

                $('#move').removeAttr('disabled')
                $('#rename').removeAttr('disabled')
                $('#delete').removeAttr('disabled')
            }
        },
        searchFor(val) {
            if (val) {
                this.updateSearchCount()
            }

            // so we dont miss with the bulk selection list
            if (!this.isBulkSelecting()) {
                this.clearSelected()
                this.selectFirst()
            }

            this.searchItemsCount = undefined
        },
        searchItemsCount(val) {
            // make sure "no_files" is hidden when search query is cleared
            if (val == undefined) {
                $('#no_files').hide()
            }
        },
        showBy(val) {
            if (val) {
                if (val == 'clear') {
                    this.showBy = null
                }

                if (!this.isBulkSelecting()) {
                    this.selectFirst()
                }
            }
        }
    },
    render () {}
}
</script>
