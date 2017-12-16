// for normal download
require('./../../vendor/download.min')

// for zip download
const JSZip = require('jszip')
const JSZipUtils = require('jszip-utils')
const FileSaver = require('file-saver')

export default {
    methods: {
        hideProgress() {
            this.progressCounter = 0
            this.showProgress = false
            this.toggleLoading()
            this.loadingFiles('hide')
            this.toggleInfo = true
        },
        // download
        saveFile(item) {
            if (this.isBulkSelecting()) {
                return typeof JSZip != 'undefined'
                    ? this.zipFiles(this.bulkList)
                    : this.downloadFiles(this.bulkList)
            }

            downloadFile(item.path)
            this.showNotif(`"${item.name}" ${this.trans('downloaded')}`)
        },
        downloadFiles(list) {
            this.showProgress = true

            let counter = 100 / list.length
            let progress = 0

            list.forEach((e) => {
                progress += counter
                this.progressCounter = `${progress}%`
                downloadFile(e.path)
            })

            this.progressCounter = '100%'
            setTimeout(() => {
                this.hideProgress()
            }, 500)

            this.showNotif('All Done')
        },
        zipFiles(list) {
            this.showProgress = true
            const manager = this

            let zip = new JSZip()
            let counter = 0
            let folders = this.folders
            let folder_name = folders.length
                ? folders[folders.length - 1]
                : 'media_manager'

            list.forEach((e) => {
                JSZipUtils.getBinaryContent(e.path, (err, data) => {
                    if (err) {
                        console.error(err)
                        this.showNotif(err, 'danger')
                    }

                    zip.file(e.name, data, {binary:true})
                    counter++

                    if (counter == list.length) {
                        zip.generateInternalStream({
                            type: 'uint8array',
                            streamFiles: true,
                            compression: 'DEFLATE',
                            platform: 'UNIX'
                        }).accumulate(function updateCallback(metadata) {
                            manager.progressCounter = `${metadata.percent}%`
                        }).then((data) => {
                            FileSaver.saveAs(new Blob([data]), `${folder_name}.zip`)

                            manager.progressCounter = '100%'
                            setTimeout(() => {
                                manager.hideProgress()
                            }, 500)

                            manager.showNotif(`"${folder_name}.zip" ${manager.trans('downloaded')}`)
                        })
                    }
                })
            })
        }
    }
}
