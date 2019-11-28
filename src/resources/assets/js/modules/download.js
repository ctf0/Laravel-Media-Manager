require('../packages/download.min')

export default {
    methods: {
        saveFile(item, e = null) {
            this.clearFocus()

            if (this.isBulkSelecting()) {
                this.bulkList.forEach((e) => {
                    if (e.type == 'folder') {
                        return this.showNotif(this.trans('sep_download'), 'warning', 5)
                    }

                    downloadFile(e.path)
                })

                return this.showNotif('All Done')
            }

            downloadFile(item.path)

            return this.showNotif(`"${item.name}" ${this.trans('downloaded')}`)
        },
        hasFolder() {
            return this.bulkList.some((e) => {
                return e.type == 'folder'
            })
        },
        clearFocus() {
            return document.activeElement.blur()
        },

        ZipDownload({target}) {
            this.clearFocus()

            this.browserSupport('Echo') && this.config.broadcasting
                ? this.showProgress = true
                : this.showNotif(this.trans('stand_by'), 'info')

            // submit form
            target.submit()
        },
        hideProgress() {
            this.progressCounter = 0
            this.showProgress = false
        }
    }
}
