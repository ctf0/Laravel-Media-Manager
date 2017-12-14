export default {
    computed: {
        filesList() {
            return this.$refs.filesList.$el.children
        },
        allFiles() {
            if (this.filteredItemsCount) {
                return this.filterdList
            }

            return this.files.items
        },
        allItemsCount() {
            if (typeof this.allFiles !== 'undefined' && this.allFiles.length > 0) {
                return this.allFiles.length
            }
        },
        filteredItemsCount() {
            if (typeof this.filterdList !== 'undefined' && this.filterdList.length > 0) {
                return this.filterdList.length
            }
        },

        // bulk
        bulkItemsCount() {
            if (typeof this.bulkList !== 'undefined' && this.bulkList.length > 0) {
                return this.bulkList.length
            }
        },
        bulkItemsSize() {
            let count = 0

            this.bulkList.map((item) => {count += item.size})

            return this.getFileSize(count)
        },
        bulkItemsChild() {
            let bulk = this.bulkItemsCount

            if (bulk) {
                if (bulk == 1 && !this.selectedFileIs('folder')) {
                    return
                }

                let count = 0

                this.bulkList.map((item) => {
                    let list = item.items

                    if (list) {
                        count += list
                    }
                })

                return count
            }
        },
        // this is made so we can still use move/delete
        // incase we have multiple files selected
        // and one or more of them is locked
        bulkListFilter() {
            return this.lockedList.length
                ? this.bulkList.filter((e) => {return !this.lockedList.includes(e.path)})
                : this.bulkList
        },

        // upload panel
        uploadPanelImg() {
            if (this.uploadToggle) {
                let imgs = this.uploadPanelImgList
                let grds = this.gradients

                let url = imgs[Math.floor(Math.random() * imgs.length)]
                let color = grds[Math.floor(Math.random() * grds.length)]

                return {
                    '--gradient': color,
                    'background-image': `url("${url}")`
                }
            }
        }
    }
}
