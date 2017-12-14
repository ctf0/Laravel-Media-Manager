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

            if (this.bulkItemsCount) {
                // select prev item
                this.selectedFile = this.bulkList[this.bulkItemsCount - 1]
            } else {
                this.resetInput(['selectedFile', 'currentFileIndex'])
            }
        },
        selectFirstInBulkList() {
            let list = this.bulkList

            if (list.length) {
                this.selectedFile = list[0]
                this.currentFileIndex = 0
            }
        }
    }
}
