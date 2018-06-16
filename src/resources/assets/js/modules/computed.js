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
            return folders.length ? this.clearDblSlash(`/${folders.join('/')}`) : 'root_'
        },

        selectedFileDimensions() {
            let f = this.dimensions.find((e) => e.url == this.selectedFile.path)

            return f ? f.val : '...'
        }
    },
    asyncComputed: {
        selectedFilePreview: {
            get() {
                if (this.selectedFile) {
                    let url = this.selectedFile.path

                    if (this.selectedFileIs('image')) {
                        if (!this.lazyModeIsOn() || !this.browserSupport('caches')) {
                            return url
                        }

                        return caches.open(this.CDBN).then((cache) => {
                            // wait until the item is cached & return it
                            return new Promise((resolve) => {
                                let t = setInterval(() => {
                                    return cache.match(url).then((response) => {
                                        return response ? response.blob() : null
                                    }).then((blob) => {
                                        if (blob) {
                                            clearInterval(t)
                                            return resolve(URL.createObjectURL(blob))
                                        }
                                    })
                                }, 250)
                            })
                        })
                    }

                    if (this.selectedFileIs('audio') && this.browserSupport('jsmediatags')) {
                        return this.getCachedResponse(url).then((res) => {
                            if (res) {
                                return res
                            }

                            return this.getAudioCover(url)
                                .then((val) => {
                                    this.cacheResponse(val, url)
                                    return val
                                }).catch((err) => {
                                    return
                                })
                        })
                    }
                }
            },
            default: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            lazy: true
        }
    }
}
