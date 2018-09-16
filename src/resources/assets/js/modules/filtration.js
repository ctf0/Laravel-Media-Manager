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

            if (item.type) {
                if (val == 'image' && mimes.image.includes(item.type)) {
                    return true
                }

                // because "pdf" shows up as "application"
                if (item.type.includes('pdf') && val != 'pdf') {
                    return false
                }

                // because "archive" shows up as "application"
                if (item.type.includes('compressed') || mimes.archive.includes(item.type)) {
                    return val == 'compressed' ? true : false
                }

                return item.type.includes(val)
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
                    } else if ('application') {
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
                !this.lazyModeIsOn()
                    ? this.selectFirst()
                    : this.lazySelectFirst()
            }

            if (this.searchFor) {
                this.updateSearchCount()
            }
        },
        filterDirList(dir) {
            // dont show dirs that have similarity with selected item(s)
            if (this.bulkItemsCount) {
                return this.bulkList.filter((e) => dir.match(`(/?)${e.name}(/?)`)).length > 0 ? false : true
            }

            return this.selectedFile && !dir.includes(this.selectedFile.name)
        },

        // search
        updateSearchCount() {
            let oldCount = this.searchItemsCount

            this.$nextTick(() => {
                this.searchItemsCount = this.filesList.length

                return this.searchItemsCount == 0
                    ? oldCount == 0 ? false : this.noSearch('show')
                    : this.noSearch('hide')
            })
        }
    }
}
