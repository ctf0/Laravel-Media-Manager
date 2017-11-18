export default {
    computed: {
        allFiles() {
            if (this.filterItemsCount) {
                return this.filterdList
            }

            return this.files.items
        },
        filterItemsCount() {
            if (typeof this.filterdList !== 'undefined' && this.filterdList.length > 0) {
                return this.filterdList.length
            }
        },
        allItemsCount() {
            if (typeof this.allFiles !== 'undefined' && this.allFiles.length > 0) {
                return this.allFiles.length
            }
        },
        bulkItemsCount() {
            if (typeof this.bulkList !== 'undefined' && this.bulkList.length > 0) {
                return this.bulkList.length
            }
        },
        bulkListFilter() {
            let list = this.bulkList

            if (this.lockedList.length) {
                list = this.bulkList.filter((x) => {
                    return this.lockedList.indexOf(x) < 0
                })
            }

            if (list.length > 0) {
                $('#delete').removeAttr('disabled')
            }

            return list
        }
    }
}
