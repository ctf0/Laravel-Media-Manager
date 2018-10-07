export default {
    methods: {
        btnFilter(val) {
            let files = this.files.items

            if (val == 'all') {
                return this.filteredItemsCount
            } else if (val == 'selected') {
                return this.bulkItemsCount && this.bulkItemsCount > 1 ? true : false
            } else if (val == 'locked') {
                return files.some((item) => {
                    return this.IsLocked(item)
                })
            } else if (val == 'application') {
                return files.some((item) => {
                    return this.fileTypeIs(item, 'application') || this.fileTypeIs(item, 'compressed')
                })
            }

            return files.some((item) => {
                return this.fileTypeIs(item, val)
            })
        },
        filterNameIs(val) {
            return this.currentFilterName == val
        },
        fileTypeIs(item, val) {
            let mimes = this.config.mimeTypes
            let type = item.type

            if (type) {
                if (val == 'image' && mimes.image.includes(type)) {
                    return true
                }

                // because "pdf" shows up as "application"
                if (type.includes('pdf') && val != 'pdf') {
                    return false
                }

                // because "archive" shows up as "application"
                if (type.includes('compressed') || mimes.archive.includes(type)) {
                    return val == 'compressed' ? true : false
                }

                return type.includes(val)
            }
        },
        showFilesOfType(val) {
            if (this.currentFilterName == val) {
                return
            }

            let files = this.files.items

            if (val == 'all') {
                this.resetInput('currentFilterName')
            } else if (val == 'locked') {
                this.filterdList = files.filter((item) => this.IsLocked(item))
                this.currentFilterName = val
            } else if (val == 'selected') {
                this.filterdList = this.bulkList
                this.currentFilterName = val
            } else {
                this.filterdList = files.filter((item) => {
                    if (val == 'text') {
                        return this.fileTypeIs(item, 'text') || this.fileTypeIs(item, 'pdf')
                    } else if (val == 'application') {
                        return this.fileTypeIs(item, 'application') || this.fileTypeIs(item, 'compressed')
                    }

                    return this.fileTypeIs(item, val)
                })

                this.currentFilterName = val
            }

            if (!this.isBulkSelecting()) {
                this.resetInput(['selectedFile', 'currentFileIndex'])
            }

            if (!this.isBulkSelecting()) {
                this.lazyModeIsOn()
                    ? this.lazySelectFirst()
                    : this.selectFirst()
            }

            if (this.searchFor) {
                this.updateSearchCount()
            }

            this.$nextTick(() => {
                EventHub.fire('start-img-observing')
            })
        },

        // search
        updateSearchCount() {
            this.$nextTick(() => {
                this.searchItemsCount = this.filesList.length

                this.$nextTick(() => {
                    if (this.searchItemsCount == 0) {
                        this.resetInput(['selectedFile', 'currentFileIndex'])

                        !this.no_search
                            ? this.noSearch('show')
                            : false
                    } else {
                        this.selectFirstInBulkList()

                        this.no_search
                            ? this.noSearch('hide')
                            : false
                    }
                })
            })
        }
    }
}
