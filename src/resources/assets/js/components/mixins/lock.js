export default {
    methods: {
        IsInLockedList(file) {
            return this.lockedList.includes(file)
        },
        toggleLock(file) {
            if (this.IsInLockedList(file)) {
                this.toggleBtns('enable')

                this.canWeMove()

                // add back to directories
                if (this.fileTypeIs(file, 'folder')) {
                    this.directories.push(file.name)
                    this.directories.sort()
                }

                return this.lockedList.splice(this.lockedList.indexOf(file), 1)
            }

            this.toggleBtns('disable')

            // remove from avail directories
            if (this.fileTypeIs(file, 'folder')) {
                this.directories.splice(this.directories.indexOf(file.name), 1)
            }
            this.lockedList.push(file)
        },
        pushToLockedList() {
            if (this.isBulkSelecting()) {
                if (this.lockedList.length) {
                    this.toggleBtns('enable')

                    let list = [...this.lockedList]

                    return list.map((e) => {
                        this.toggleLock(e)
                    })
                }

                this.toggleBtns('disable')
                return this.bulkList.map((e) => {
                    this.toggleLock(e)
                })
            }

            this.toggleLock(this.selectedFile)
        },
        toggleBtns(s) {
            if (s == 'disable') {
                $('#move').attr('disabled', true)
                $('#rename').attr('disabled', true)
                return $('#delete').attr('disabled', true)
            }

            $('#move').removeAttr('disabled')
            $('#rename').removeAttr('disabled')
            $('#delete').removeAttr('disabled')
        }
    }
}
