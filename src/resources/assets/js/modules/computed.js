export default {
    computed: {
        filesList() {
            return this.$refs.filesList.children
        },
        allFiles() {
            if (this.filteredItemsCount) {
                return this.filterdList
            }

            return this.files.items
        },
        allItemsCount() {
            if (typeof this.allFiles !== 'undefined' && this.allFiles.length > 0) {
                return this.allFiles.length
            }
        },
        filteredItemsCount() {
            if (typeof this.filterdList !== 'undefined' && this.filterdList.length > 0) {
                return this.filterdList.length
            }
        },

        // bulk
        bulkItemsCount() {
            if (typeof this.bulkList !== 'undefined' && this.bulkList.length > 0) {
                return this.bulkList.length
            }
        },
        bulkItemsSize() {
            let count = 0

            this.bulkList.forEach((item) => {
                count += parseInt(item.size)
            })

            return count !== 0 ? this.getFileSize(count) : false
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
        },
        bulkItemsFilter() {
            return this.lockedList.length
                ? this.bulkList.filter((e) => !this.IsLocked(e.path))
                : this.bulkList
        },
        bulkItemsFilterSize() {
            let count = 0
            this.bulkItemsFilter.map((item) => {count += item.size})
            return count !== 0 ? this.getFileSize(count) : false
        },

        // upload panel
        uploadPanelImg() {
            if (this.toggleUploadArea) {
                let imgs = this.uploadPanelImgList
                let grds = this.uploadPanelGradients

                let url = imgs[Math.floor(Math.random() * imgs.length)]
                let color = grds[Math.floor(Math.random() * grds.length)]

                return {
                    '--gradient': color,
                    'background-image': `url("${url}")`
                }
            }
        },

        // caching
        CDBN() {
            return 'ctf0-Media_Manager'
        },
        cacheName() {
            let folders = this.folders
            return folders.length ? '/' + folders.join('/') : 'root_'
        }
    },
    asyncComputed: {
        selectedFilePreview() {
            if (this.selectedFileIs('image')) {
                let url = this.selectedFile.path

                if ( !this.config.lazyLoad || this.config.lazyLoad && !('caches' in window) ) {
                    return url
                }

                // get cache or serve the url
                // warning: url is now being downloaded twice
                return caches.open(this.CDBN).then((cache) => {
                    return cache.match(url).then((response) => {
                        return response ? response.blob() : url
                    }).then((blob) => {
                        if (blob && blob.size) {
                            return URL.createObjectURL(blob)
                        } else {
                            return blob
                        }
                    })
                })
            }
        }
    }
}