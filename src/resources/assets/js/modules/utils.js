export default {
    methods: {
        /*                Check                */
        isLastItem(item, list) {
            return item == list[list.length - 1]
        },
        isActiveModal(el) {
            return this.activeModal == el
        },
        moveUpCheck() {
            return this.allItemsCount && this.folders.length
        },

        /*                Buttons                */
        item_ops() {
            if (this.isBulkSelecting()) {
                return this.bulkItemsFilter.length == 0
            }

            return !this.selectedFile || this.IsLocked(this.selectedFile)
        },
        lock_btn() {
            return this.searchItemsCount == 0 ||
                this.isLoading ||
                !this.allItemsCount ||
                this.isBulkSelecting() && !this.bulkItemsCount
        },
        vis_btn() {
            return this.searchItemsCount == 0 ||
            this.isLoading ||
            !this.allItemsCount ||
            !this.isBulkSelecting() && this.selectedFileIs('folder') ||
            this.isBulkSelecting() && !this.bulkItemsCount
        },
        reset() {
            this.bulkSelect = false
            this.bulkSelectAll = false
            this.resetInput('bulkList', [])
            this.resetInput('searchFor')

            !this.lazyModeIsOn()
                ? this.selectFirst()
                : this.lazySelectFirst()
        },
        clearAll() {
            if (!this.isLoading) {
                this.clearUrlQuery()
                this.clearLs()
                this.clearCache()
                this.clearImageCache()
            }
        },

        /*                Resolve                */
        getFileName(name) {
            if (!this.config.hideFilesExt) {
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
        getIndexRange(start, end) {
            return Array(end - start + 1).fill().map((_, idx) => start + idx)
        },
        getElementByIndex(i) {
            return document.querySelector(`[data-file-index='${i}']`)
        },
        getAudioCover(url) {
            return new Promise((resolve, reject) => {
                jsmediatags.read(url, {
                    onSuccess(tag) {
                        if (tag.tags.picture) {
                            const {data, format} = tag.tags.picture
                            let base64String = ''

                            for (var value of data) {
                                base64String += String.fromCharCode(value)
                            }

                            return resolve(`data:${format};base64,${window.btoa(base64String)}`)
                        }

                        return reject('no cover found')
                    },
                    onError(error) {
                        return reject(error)
                    }
                })
            })
        },
        trans(key) {
            return this.translations[key]
        },

        /*                Toggle                */
        toggleModal(selector = null) {
            if (!selector) {
                // refresh if an image was edited
                if (this.activeModal == 'imageEditor_modal' && this.imageWasEdited) {
                    this.imageWasEdited = false
                    this.refresh()
                }

                this.resetInput('activeModal')
                return EventHub.fire('modal-hide')
            }

            this.activeModal = selector
            EventHub.fire('modal-show')
        },
        toggleLoading() {
            return this.isLoading = !this.isLoading
        },
        toggleInfoPanel() {
            return this.toggleInfo = !this.toggleInfo
        },
        toggleUploadPanel() {
            return this.toggleUploadArea = !this.toggleUploadArea
        },
        toggleLoader(key, state) {
            return this[key] = state
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
            this.toggleInfo = false
            this.toggleLoader('ajax_error', true)
            EventHub.fire('ajax-error-show')
        },

        /*                Helpers                */
        copyLink(path) {
            this.linkCopied = true
            this.$copyText(path)
        },
        browserSupport(api) {
            return api in window
        },
        resetInput(input, val = null) {
            if (Array.isArray(input)) {
                return input.forEach((e) => {
                    this[e] = val
                })
            }

            this[input] = val
        },
        clearDblSlash(str) {
            str = str.replace(/\/+/g, '/')
            str = str.replace(/:\//, '://')

            return str
        },
        arrayFilter(arr) {
            return arr.filter((e) => e)
        },
        showNotif(msg, s = 'success', duration = 3) {
            let title

            switch (s) {
                case 'black':
                case 'danger':
                    title = 'Error'
                    duration = null
                    break
                case 'warning':
                    title = 'Warning'
                    break
                case 'info':
                    title = 'Info'
                    duration = 5
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
        }
    }
}
