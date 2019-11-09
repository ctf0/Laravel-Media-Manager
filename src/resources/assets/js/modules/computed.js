import {loadImageWithWorker} from '../webworkers/image'

export default {
    computed: {
        filesList() {
            return this.$refs.filesList.children
        },
        filesNamesList() {
            return this.files.items.map((item) => {
                return item.name
            })
        },
        allFiles() {
            if (this.filteredItemsCount) {
                return this.filterdList
            }

            return this.files.items
        },
        allItemsCount() {
            if (typeof this.allFiles !== 'undefined') {
                return this.allFiles.length
            }
        },
        filteredItemsCount() {
            if (typeof this.filterdList !== 'undefined') {
                return this.filterdList.length
            }
        },

        // bulk
        bulkItemsCount() {
            if (typeof this.bulkList !== 'undefined' && this.bulkList.length > 0) {
                return this.bulkList.length
            }

            return null
        },
        bulkItemsSize() {
            let count = 0

            this.bulkList.forEach((item) => {
                count += parseInt(item.size)
            })

            return count !== 0 ? this.getFileSize(count) : null
        },
        bulkItemsChild() {
            let bulk = this.bulkItemsCount

            if (bulk) {
                if (bulk == 1 && !this.selectedFileIs('folder')) {
                    return
                }

                let count = 0

                this.bulkList.map((item) => {
                    let list = item.items

                    if (list) {
                        count += list
                    }
                })

                return count
            }

            return null
        },
        bulkItemsFilter() {
            return this.lockedList.length
                ? this.bulkList.filter((e) => !this.IsLocked(e.path))
                : this.bulkList
        },
        bulkItemsFilterSize() {
            let count = 0
            this.bulkItemsFilter.map((item) => {count += item.size})

            return count !== 0 ? this.getFileSize(count) : null
        },

        // upload panel
        uploadPanelImg() {
            if (this.UploadArea) {
                let imgs = this.uploadPanelImgList
                let grds = this.uploadPanelGradients

                let url = imgs.length ? imgs[Math.floor(Math.random() * imgs.length)] : null
                let color = grds[Math.floor(Math.random() * grds.length)]

                return url
                    ? {'--gradient': color, 'background-image': `url("${url}")`}
                    : {'--gradient': color}
            }

            return null
        },
        uploadPreviewListSize() {
            let size = this.uploadPreviewList
                .map((el) => el.size)
                .reduce((a, b) => a + b, 0)

            return this.getFileSize(size)
        },

        // misc
        selectedFileDimensions() {
            let f = this.dimensions.find((e) => e.url == this.selectedFile.path)

            return f ? f.val : null
        }
    },
    asyncComputed: {
        selectedFilePreview: {
            get() {
                if (this.selectedFile) {
                    let url = this.selectedFile.path

                    if (this.selectedFileIs('image')) {
                        return loadImageWithWorker(url).then((img) => {
                            return img
                        })
                    }

                    if (this.selectedFileIs('audio') && this.browserSupport('jsmediatags')) {
                        return this.getAudioData(url).then((val) => {
                            return val.picture
                        })
                    }
                }
            },
            watch: ['selectedFile'],
            default: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            lazy: true
        }
    }
}
