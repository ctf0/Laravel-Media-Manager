export default {
    methods: {
        isBulkSelecting() {
            return this.bulkSelect
        },
        IsInBulkList(file) {
            return this.bulkList.includes(file)
        },
        pushtoBulkList(file) {
            if (!this.bulkItemsCount || !this.IsInBulkList(file)) {
                return this.bulkList.push(file)
            }

            this.bulkList.splice(this.bulkList.indexOf(file), 1)

            // select prev item
            this.selectedFile = this.bulkList[this.bulkItemsCount - 1]
        }
    },

    /*                Ops                */
    blkSlct() {
        this.bulkSelect = !this.bulkSelect

        // reset when toggled off
        if (this.isBulkSelecting()) {
            return this.clearSelected()
        }

        this.bulkSelectAll = false
        this.clearSelected()
        this.resetInput('bulkList', [])
        this.selectFirst()
    },
    blkSlctAll() {
        // if no items in bulk list
        if (this.bulkList == 0) {
            // if no search query
            if (!this.searchFor) {
                this.bulkSelectAll = true
                this.bulkList = this.allFiles.slice(0)
            }

            // if found search items
            if (this.searchItemsCount) {
                this.bulkSelectAll = true
                $('#files li').each(function() {
                    $(this).trigger('click')
                })
            }
        }

        // if having search + having bulk items < search found items
        else if (this.searchFor && this.bulkItemsCount < this.searchItemsCount) {
            this.resetInput('bulkList', [])
            this.clearSelected()

            if (this.bulkSelectAll) {
                this.bulkSelectAll = false
            } else {
                this.bulkSelectAll = true
                $('#files li').each(function() {
                    $(this).trigger('click')
                })
            }
        }

        // if NO search + having bulk items < all items
        else if (!this.searchFor && this.bulkItemsCount < this.allItemsCount) {
            if (this.bulkSelectAll) {
                this.bulkSelectAll = false
                this.resetInput('bulkList', [])
            } else {
                this.bulkSelectAll = true
                this.bulkList = this.allFiles.slice(0)
            }

            this.clearSelected()
        }

        // otherwise
        else {
            this.bulkSelectAll = false
            this.resetInput('bulkList', [])
            this.clearSelected()
        }

        // if we have items in bulk list, select first item
        if (this.bulkItemsCount) {
            this.selectedFile = this.bulkList[0]
        }
    }
}
