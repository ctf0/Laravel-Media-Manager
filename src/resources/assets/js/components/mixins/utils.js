require('./../../vendor/download.min')

export default {
    methods: {
        isLastItem(item, list) {
            return item == list[list.length - 1]
        },
        isActiveModal(el) {
            return this.active_modal == el
        },
        showNotif(msg, s = 'success') {

            let title
            let duration = 3

            switch (s) {
            case 'black':
            case 'danger':
                title = 'Error'
                duration = null
                break
            case 'warning':
                title = 'Warning'
                break
            default:
                title = 'Success'
            }

            EventHub.fire('showNotif', {
                title: title,
                body: msg,
                type: s,
                duration: duration
            })
        },
        resetInput(input, val = null) {
            if (Array.isArray(input)) {
                return input.forEach((e) => {
                    this[e] = val
                })
            }

            this[input] = val
        },
        moveUpCheck() {
            return this.allItemsCount && this.folders.length && !this.restrictAndLast()
        },

        /*                Buttons                */
        mv_dl() {
            if (this.isBulkSelecting()) {
                return this.bulkListFilter.length == 0
            }

            return !this.selectedFile || this.IsInLockedList(this.selectedFile)
        },
        lock() {
            return this.searchItemsCount == 0 ||
            this.isLoading ||
            !this.allItemsCount ||
            this.isBulkSelecting() && !this.bulkItemsCount
        },
        reset() {
            this.bulkSelect = false
            this.bulkSelectAll = false
            this.resetInput('bulkList', [])
            this.resetInput(['selectedFile', 'currentFileIndex', 'searchFor'])
        },

        /*                Resolve                */
        getFileName(name) {
            if (!this.hideFilesExt) {
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
        toggleModal(selector = null) {
            if (!selector) {
                this.noScroll('remove')
                this.resetInput('active_modal')
                return EventHub.fire('modal-hide')
            }

            this.noScroll('add')
            this.active_modal = selector
            EventHub.fire('modal-show')
        },
        toggleLoading() {
            return this.isLoading = !this.isLoading
        },
        toggleInfoPanel() {
            return this.toggleInfo = !this.toggleInfo
        },
        toggleUploadPanel() {
            this.toggleUploadArea = !this.toggleUploadArea
        },
        toggleLoader(key, state) {
            this[key] = state
        },

        /*                Loading                */
        noScroll(s) {
            let el = document.getElementsByTagName('html')[0]

            if (s == 'add') {
                return el.classList.add('no-scroll')
            }

            return el.classList.remove('no-scroll')
        },
        noFiles(s) {
            if (s == 'show') {
                this.toggleInfo = false
                this.toggleLoader('no_files', true)
                return EventHub.fire('no-files-show')
            }

            this.toggleInfo = true
            this.toggleLoader('no_files', false)
            EventHub.fire('no-files-hide')
        },
        noSearch(s) {
            if (s == 'show') {
                this.toggleLoader('no_search', true)
                return EventHub.fire('no-search-show')
            }

            this.toggleLoader('no_search', false)
            EventHub.fire('no-search-hide')
        },
        loadingFiles(s) {
            if (s == 'show') {
                this.toggleLoader('loading_files', true)
                return EventHub.fire('loading-files-show')
            }

            this.toggleLoader('loading_files', false)
            EventHub.fire('loading-files-hide')
        },
        ajaxError() {
            this.toggleInfoPanel()
            this.toggleInfo = false
            this.toggleLoader('ajax_error', true)
            EventHub.fire('ajax-error-show')
        },

        /*                Ops                */
        // copy to clipboard
        copyLink(path) {
            this.linkCopied = true
            this.$copyText(path)
        },
        // download
        saveFile(item) {
            if (this.isBulkSelecting()) {
                this.bulkList.forEach((e) => {
                    downloadFile(e.path)
                })

                return this.showNotif('All Done')
            }

            downloadFile(item.path)
            return this.showNotif(`"${item.name}" ${this.trans('downloaded')}`)
        },

        // ls
        updateLs(obj) {
            let oldLs = this.$ls.get('mediamanager', {})

            Object.assign(oldLs, obj)
            this.$ls.set('mediamanager', oldLs)
        }
    }
}
