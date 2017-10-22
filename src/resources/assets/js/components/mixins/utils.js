require('./../../vendor/download.min')

export default {
    methods: {
        isLastItem(item, list) {
            return item == list[list.length - 1]
        },
        lightBoxIsActive() {
            return $('#img_modal').hasClass('is-active')
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
        checkForFolders() {
            return $('#move_folder_dropdown').val() !== null
                ? true
                : false
        },
        resetInput(input, val = undefined) {
            this[input] = val
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

        /*                Toggle                */
        toggleInfo() {
            $('#right').fadeToggle()
            let span = $('.toggle').find('span').not('.icon')
            span.text(span.text() == 'Close' ? 'Open' : 'Close')
            $('.toggle').find('.fa').toggleClass('fa fa-angle-double-right').toggleClass('fa fa-angle-double-left')
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
                EventHub.fire('no-files-show')
                return $('#no_files').fadeIn()
            }

            EventHub.fire('no-files-hide')
            $('#no_files').hide()
        },
        loadingFiles(s) {
            if (s == 'show') {
                EventHub.fire('loading-files-show')
                return $('#file_loader').show()
            }

            EventHub.fire('loading-files-hide')
            $('#file_loader').hide()
        },
        ajaxError() {
            EventHub.fire('ajax-error-show')
            $('#ajax_error').show()
        },

        // download
        // DownloadJS v0.5.2
        saveFile(path) {
            if (this.isBulkSelecting()) {
                this.bulkList.forEach((item) => {
                    downloadFile(item.path)
                })

                return this.showNotif('All Done')
            }

            downloadFile(path)
        }
    }
}
