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

        ZipDownload(type, id) {
            if (!this.browserSupport('EventSource')) {
                this.showProgress = true

                // de-select download btn
                Array.from(document.querySelectorAll('.zip')).forEach((e) => {
                    e.blur()
                })

                let folders = this.folders
                let name = type == 'folder'
                    ? this.selectedFile.name
                    : folders.length ? `${folders[folders.length - 1]}-files` : 'media_manager-files'

                let es = new EventSource(
                    `${this.routes.zipProgress}/${name}/${id}`,
                    {withCredentials: true}
                )

                // events
                es.addEventListener('progress', (e) => {
                    this.progressCounter = `${this.getESData(e)}%`
                }, false)

                es.addEventListener('warn', (e) => {
                    this.showNotif(this.getESData(e), 'warning')
                }, false)

                es.addEventListener('done', (e) => {
                    this.showNotif(this.getESData(e))
                    setTimeout(() => {
                        this.hideProgress(es)
                    }, 1000)
                }, false)

                es.addEventListener('exit', () => {
                    this.showNotif(this.trans('stream_exit_error'), 'danger')
                    this.hideProgress(es)
                }, false)

                // error
                es.addEventListener('error', (e) => {
                    if (e.readyState == EventSource.CLOSED) {
                        this.hideProgress(es)
                        console.error(`Error: connection terminated, ${EventSource}`)
                    }
                }, false)
            } else {
                this.showNotif(`"EventSource is not supported" ${this.trans('stand_by')}`)
            }
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
