export default {
    methods: {
        isLastItem(item, list) {
            return item == list[list.length - 1]
        },
        lightBoxIsActive() {
            return $('#img_modal').hasClass('is-active')
        },
        showNotif(data) {
            EventHub.fire('showNotif', {
                title: data.title,
                body: data.body,
                type: data.type,
                duration: data.duration !== undefined ? data.duration : null
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

            let k = 1000,
                dm = 2,
                sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                i = Math.floor(Math.log(bytes) / Math.log(k))

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
                EventHub.fire('modal-hide')
                this.noScroll()
                return $('.modal').removeClass('is-active')
            }

            EventHub.fire('modal-show')
            this.noScroll('add')
            $(selector).addClass('is-active')
            $(selector).find('input').focus()
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
        }
    }
}
