export default {
    computed: {
        bulkItemsCount() {
            return this.bulkList.length
        },
        bulkItemsSize() {
            return this.getListTotalSize(this.bulkList)
        },
        bulkItemsChild() {
            let bulk = this.bulkItemsCount
            let count = 0

            if (bulk) {
                if (bulk == 1 && !this.selectedFileIs('folder')) return

                this.bulkList.map((item) => {
                    let list = item.count

                    if (list) {
                        count += list
                    }
                })
            }

            return count
        },
        bulkItemsFilter() {
            return this.lockedList.length
                ? this.bulkList.filter((e) => !this.IsLocked(e.path))
                : this.bulkList
        },
        bulkItemsFilterSize() {
            return this.getListTotalSize(this.bulkItemsFilter)
        }
    },
    methods: {
        // ops
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

            this.bulkItemsCount
                ? this.selectedFile = this.bulkList[this.bulkItemsCount - 1] // select prev item
                : this.resetInput(['selectedFile', 'currentFileIndex'])
        },
        selectFirstInBulkList() {
            let list = this.bulkList

            if (list.length) {
                this.selectedFile = list[0]
                this.currentFileIndex = 0
            }
        },

        // btns
        blkSlct() {
            this.bulkSelect = !this.bulkSelect
            this.bulkSelectAll = false
            this.resetInput('bulkList', [])

            let file = this.selectedFile
            let index = this.currentFileIndex

            this.resetInput(['selectedFile', 'currentFileIndex'])

            if (!this.isBulkSelecting()) {
                file
                    ? this.setSelected(file, index)
                    : this.selectFirst()
            }
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
                if (this.searchFor && this.searchItemsCount) {
                    this.bulkSelectAll = true

                    let list = this.filesList
                    for (let i = list.length - 1; i >= 0; i--) {
                        list[i].click()
                    }
                }
            }

            // if having search + having bulk items < search found items
            else if (this.searchFor && this.bulkItemsCount < this.searchItemsCount) {
                this.resetInput('bulkList', [])
                this.resetInput(['selectedFile', 'currentFileIndex'])

                if (this.bulkSelectAll) {
                    this.bulkSelectAll = false
                } else {
                    this.bulkSelectAll = true

                    let list = this.filesList
                    for (let i = list.length - 1; i >= 0; i--) {
                        list[i].click()
                    }
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

                this.resetInput(['selectedFile', 'currentFileIndex'])
            }

            // otherwise
            else {
                this.bulkSelectAll = false
                this.resetInput('bulkList', [])
                this.resetInput(['selectedFile', 'currentFileIndex'])
            }

            // if we have items in bulk list, select first item
            if (this.bulkItemsCount) {
                this.selectedFile = this.bulkList[0]
            }
        }
    }
}
