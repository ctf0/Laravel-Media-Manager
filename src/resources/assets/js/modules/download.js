require('../packages/download.min')

export default {
    methods: {
        saveFile(item) {
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

        ZipDownload({target}) {
            // de-select download btn
            Array.from(document.querySelectorAll('.zip')).forEach((e) => {
                e.blur()
            })

            this.browserSupport('Echo') && this.config.broadcasting
                ? this.showProgress = true
                : this.showNotif(this.trans('stand_by'), 'info')

            // submit form
            target.submit()
        },
        hideProgress() {
            this.progressCounter = 0
            this.showProgress = false
            this.isLoading = false
            this.toggleInfo = true
            this.loadingFiles('hide')
        }
    }
}
