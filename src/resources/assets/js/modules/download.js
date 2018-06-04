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

                this.$refs['success-audio'].play()
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

            if (this.browserSupport('Echo')) {
                // show progress
                this.showProgress = true

                // listen to changes
                Echo.private(`User.${this.userId}.media`)
                    .listen('.user.media.zip', ({data}) => {
                        if (data.progress) {
                            this.progressCounter = `${data.progress}%`

                            if (data.progress >= 100) {
                                setTimeout(() => {
                                    this.hideProgress()
                                }, 1000)
                            }
                        }

                        if (data.type == 'warn') {
                            this.showNotif(data.msg, 'warning')
                        }
                    })
            }

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
