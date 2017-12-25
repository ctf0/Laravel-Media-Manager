require('./../../packages/download.min')

export default {
    methods: {
        saveFile(item) {
            if (this.isBulkSelecting()) {
                this.bulkList.forEach((e) => {
                    if (e.type == 'folder') {
                        return this.showNotif(this.trans('sep_download'), 'warning')
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

        ZipDownload(type) {
            this.showProgress = true

            // de-select download btn
            this.$refs['zip'].forEach((e) => {
                e.blur()
            })

            let folders = this.folders
            let name = type == 'folder'
                ? this.selectedFile.name
                : folders.length ? `${folders[folders.length - 1]}-files` : 'media_manager-files'

            let es = new EventSource(
                `${this.zipProgressRoute}/${name}`,
                {withCredentials: true}
            )

            // events
            es.addEventListener('progress', (e) => {
                this.progressCounter = `${this.getESData(e)}%`
            }, false)

            es.addEventListener('warn', (e) => {
                this.showNotif(this.getESData(e), 'danger')
            }, false)

            es.addEventListener('done', (e) => {
                this.showNotif(this.getESData(e))
                setTimeout(() => {
                    this.hideProgress(es)
                }, 1000)
            }, false)

            es.addEventListener('exit', () => {
                console.warn('Error: script took too long, listener terminated')
                this.hideProgress(es)
            }, false)

            // error
            es.addEventListener('error', (e) => {
                if (e.readyState == EventSource.CLOSED) {
                    this.hideProgress(es)
                    console.error(`Error: connection terminated, ${EventSource}`)
                }
            }, false)
        },
        getESData(e, item = 'response') {
            let data = JSON.parse(e.data)

            return item ? data[item] : data
        },
        hideProgress(es) {
            es.close()
            this.progressCounter = 0
            this.showProgress = false
            this.toggleInfo = true
            this.toggleLoading()
            this.loadingFiles('hide')
        }
    }
}
