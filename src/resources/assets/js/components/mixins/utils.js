require('./../../vendor/download.min')

export default {
    methods: {
        isLastItem(item, list) {
            return item == list[list.length - 1]
        },
        lightBoxIsActive() {
            return $('#preview_modal').hasClass('is-active')
        },
        showNotif(msg, s = 'success') {

            let title = ''
            let duration = null

            switch (s) {
            case 'danger':
                title = 'Error'
                break
            case 'warning':
                title = 'Warning'
                duration = 3
                break
            default:
                title = 'Success'
                duration = 3
            }

            EventHub.fire('showNotif', {
                title: title,
                body: msg,
                type: s,
                duration: duration
            })
        },
        resetInput(input, val = undefined) {
            this[input] = val
        },

        // moving
        checkForFolders() {
            return $('#move_folder_dropdown').val() !== null ? true : false
        },
        moveUpCheck() {
            return this.folders.length && !this.restrictAndLast()
        },
        canWeMove() {
            this.$nextTick(() => {
                setTimeout(() => {
                    if (!this.checkForFolders()) {
                        $('#move').attr('disabled', true)
                    }
                }, 50)
            })
        },

        /*                Resolve                */
        getFileName(name) {
            if (!this.hideExt) {
                return name
            }

            return name.replace(/(.[^.]*)$/, '')
        },
        getFileSize(bytes) {
            if (bytes === 0) {
                return 'N/A'
            }

            let k = 1000
            let dm = 2
            let sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
            let i = Math.floor(Math.log(bytes) / Math.log(k))

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
        },
        getExtension(name) {
            let index = name.lastIndexOf('.')

            return index > 0 ? name.substring(index + 1) : null
        },
        trans(key) {
            return this.mediaTrans[key]
        },

        /*                Toggle                */
        toggleInfo() {
            $('#right').fadeToggle('fast')

            let btn = $('.toggle')
            let span = btn.find('span').not('.icon')

            span.text(span.text() == this.trans('close') ? this.trans('open') : this.trans('close'))
            btn.find('.fa').toggleClass('fa-angle-double-right fa-angle-double-left')
        },
        toggleModal(selector = null) {
            if (!selector) {
                this.noScroll()
                $('.mm-modal').removeClass('is-active')
                return EventHub.fire('modal-hide')
            }

            this.noScroll('add')
            $(selector).addClass('is-active')
            $(selector).find('input').focus()
            EventHub.fire('modal-show')
        },

        // loading
        toggleLoading() {
            return this.is_loading = !this.is_loading
        },
        noScroll(s) {
            if (s == 'add') {
                return $('html').addClass('no-scroll')
            }

            $('html').removeClass('no-scroll')
        },
        noFiles(s) {
            if (s == 'show') {
                $('.toggle').fadeOut('fast')
                $('#no_files').show()
                return EventHub.fire('no-files-show')
            }

            $('#no_files').hide()
            $('.toggle').fadeIn('fast')
            EventHub.fire('no-files-hide')
        },
        loadingFiles(s) {
            if (s == 'show') {
                $('#file_loader').show()
                return EventHub.fire('loading-files-show')
            }

            $('#file_loader').hide()
            EventHub.fire('loading-files-hide')
        },
        ajaxError() {
            $('.toggle').fadeOut('fast')
            $('#ajax_error').show()
            EventHub.fire('ajax-error-show')
        },

        // download
        saveFile(path) {
            if (this.isBulkSelecting()) {
                this.bulkList.forEach((item) => {
                    downloadFile(item.path)
                })

                return this.showNotif('All Done')
            }

            downloadFile(path)
        },

        // copy to clipboard
        copyLink(path) {
            this.linkCopied = true
            this.$copyText(path)
        },

        // animation
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
    }
}
