export default {
    methods: {
        IsInLockedList(file) {
            return this.lockedList.includes(file)
        },
        toggleLock(file) {
            if (this.IsInLockedList(file)) {
                this.canWeMove()

                // add back to directories
                if (this.fileTypeIs(file, 'folder')) {
                    this.directories.push(file.name)
                    this.directories.sort()
                }

                // remove item
                return this.lockedList.splice(this.lockedList.indexOf(file), 1)
            }

            // remove from directories
            if (this.fileTypeIs(file, 'folder')) {
                this.directories.splice(this.directories.indexOf(file.name), 1)
            }

            // add item
            this.lockedList.push(file)
        },
        pushToLockedList() {
            if (this.isBulkSelecting()) {
                // clear prev
                if (this.lockedList.length) {
                    let list = this.lockedList.slice(0)

                    return list.map((e) => {
                        this.toggleLock(e)
                    })
                }

                // add selected
                return this.bulkList.map((e) => {
                    this.toggleLock(e)
                })
            }

            if (this.selectedFile) {
                this.toggleLock(this.selectedFile)
            }
        }
    }
}
