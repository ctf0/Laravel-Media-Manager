export default {
    watch: {
        allFiles(val) {
            if (val.length < 1) {
                this.noFiles('show')
            }
        },
        selectedFile(val) {
            if (this.inModal && !this.selectedFileIs('folder')) {
                EventHub.fire('file_selected', val.path)
            }

            // hide move button when there is only one folder and its selected
            this.canWeMove()
        },

        // bulk
        bulkList(val) {
            if (val) {
                // hide move button when all folders are selected
                this.canWeMove()
            }
        },
        bulkItemsCount(val) {
            if (val > 1 && !this.bulkSelectAll) {
                this.bulkSelectAll = true
            }
        },

        // filter
        sortBy(val) {
            if (val) {
                if (val == 'clear') {
                    this.resetInput('sortBy')
                }

                if (!this.isBulkSelecting()) {
                    this.selectFirst()
                }
            }
        },
        currentFilterName(val) {
            if (!val) {
                this.resetInput('filterdList', [])
            }
        },

        // search
        searchFor(val) {
            if (!this.isBulkSelecting()) {
                this.selectFirst()
            }

            if (!val) {
                this.resetInput('searchItemsCount')
                return this.noFiles('hide')
            }
        },
        searchItemsCount(val) {
            if (val == 0) {
                this.clearSelected()
            }

            if (this.allItemsCount == undefined) {
                this.resetInput('searchItemsCount')
            }
        }
    }
}
