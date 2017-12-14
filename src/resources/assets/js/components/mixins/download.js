// for normal download
require('./../../vendor/download.min')

// for zip download
const JSZip = require('jszip')
const JSZipUtils = require('jszip-utils')
const FileSaver = require('file-saver')

export default {
    methods: {
        // download
        saveFile(item) {
            if (this.isBulkSelecting()) {
                return typeof JSZip != 'undefined'
                    ? this.zipFiles(this.bulkList)
                    : this.downloadFiles(this.bulkList)
            }

            this.downloadFiles([item])
            this.showNotif(`"${item.name}" ${this.trans('downloaded')}`)
        },
        downloadFiles(list) {
            list.forEach((e) => {
                downloadFile(e.path)
            })
            this.showNotif('All Done')
        },
        zipFiles(list) {
            let zip = new JSZip()
            let count = 0

            let folders = this.folders
            let folder_name = folders.length
                ? folders[folders.length - 1]
                : 'media_manager'

            list.forEach((e) => {
                JSZipUtils.getBinaryContent(e.path, (err, data) => {
                    if (err) {
                        console.error(err)
                        this.showNotif(this.trans('ajax_error'), 'danger')
                    }

                    zip.file(e.name, data, {binary:true})
                    count++

                    if (count == list.length) {
                        zip.generateAsync({
                            type:'blob',
                            compression: 'DEFLATE',
                            platform: 'UNIX'
                        }).then((content) => {
                            FileSaver.saveAs(content, `${folder_name}.zip`)
                            this.showNotif(`"${folder_name}.zip" ${this.trans('downloaded')}`)
                        })
                    }
                })
            })
        }
    }
}
