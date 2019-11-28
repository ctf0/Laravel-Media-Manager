export default {
    computed: {
        filesList() {
            return this.$refs.filesList.children
        },
        allFiles() {
            if (this.filteredItemsCount) {
                return this.filterdList
            }

            return this.files.items
        },
        filesNamesList() {
            return this.allFiles.map((item) => {
                if (item.type != 'folder') {
                    return item.name
                }
            }).filter((e) => e)
        },
        allItemsCount() {
            if (typeof this.allFiles !== 'undefined') {
                return this.allFiles.length
            }

            return 0
        },
        filteredItemsCount() {
            if (typeof this.filterdList !== 'undefined') {
                return this.filterdList.length
            }

            return 0
        }
    }
}
