export default {
    methods: {
        isBulkSelecting() {
            return $('#blk_slct').hasClass('is-danger')
        },
        IsInBulkList(file) {
            return this.bulkList.includes(file)
        },
        pushtoBulkList(file) {
            if (!this.bulkItemsCount) {
                return this.bulkList.push(file)
            }

            if (!this.bulkList.includes(file)) {
                return this.bulkList.push(file)
            }

            this.bulkList.splice(this.bulkList.indexOf(file), 1)

            // select prev item
            if (this.bulkItemsCount) {
                return this.selectedFile = this.bulkList[this.bulkItemsCount - 1]
            }

            // clear slection
            this.clearSelected()
        }
    }
}
