export default {
    methods: {
        IsInLockedList(file) {
            if (file) {
                return this.lockedList.includes(file.path)
            }
        },
        toggleLock(file) {
            // remove item
            if (this.IsInLockedList(file)) {
                if (this.fileTypeIs(file, 'folder')) {
                    this.directories.push(file.name)
                    this.directories.sort()
                }

                this.lockForm(file.path, 'unlocked')
                return this.lockedList.splice(this.lockedList.indexOf(file.path), 1)
            }

            // add item
            if (this.fileTypeIs(file, 'folder')) {
                this.directories.splice(this.directories.indexOf(file.name), 1)
            }

            this.lockForm(file.path, 'locked')
            this.lockedList.push(file.path)
        },
        pushToLockedList() {
            if (this.isBulkSelecting()) {
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
