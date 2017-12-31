export default {
    methods: {
        btnFilter(val) {
            if (val == 'all') {
                return this.filteredItemsCount
            }

            return this.files.items.some((item) => {
                return this.fileTypeIs(item, val)
            })
        },
        filterNameIs(val) {
            return this.currentFilterName == val
        },
        fileTypeIs(item, val) {
            return item.type.includes(val)
        },
        showFilesOfType(val) {
            if (this.currentFilterName == val) {
                return
            }

            if (val == 'all') {
                this.resetInput('currentFilterName')
            } else {
                this.filterdList = this.files.items.filter((item) => {
                    if (val == 'text') {
                        return this.fileTypeIs(item, 'text') || this.fileTypeIs(item, 'pdf')
                    }

                    return this.fileTypeIs(item, val)
                })

                this.currentFilterName = val
            }

            if (!this.isBulkSelecting()) {
                this.resetInput(['selectedFile', 'currentFileIndex'])
                this.selectFirst()
            }

            if (this.searchFor) {
                this.updateSearchCount()
            }
        },
        filterDirList(dir) {
            // dont show dirs that have similarity with selected item(s)
            if (this.bulkItemsCount) {
                if (this.bulkList.filter((e) => dir.match(`(/?)${e.name}(/?)`)).length > 0) {
                    return false
                }

                return true
            }

            return this.selectedFile && !dir.includes(this.selectedFile.name)
        },

        /*                Search                */
        updateSearchCount() {
            let oldCount = this.searchItemsCount

            this.$nextTick(() => {
                this.searchItemsCount = this.filesList.length

                if (this.searchItemsCount == 0) {
                    if (oldCount == 0) {
                        return
                    }

                    return this.noSearch('show')
                }

                this.noSearch('hide')
            })
        }
    }
}
