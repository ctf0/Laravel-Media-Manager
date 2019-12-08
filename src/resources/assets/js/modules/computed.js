export default {
    computed: {
        filesList() {
            return this.$refs.filesList.children
        },
        allFiles() {
            if (this.filteredItemsCount) {
                return this.filterdFilesList
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
            if (typeof this.filterdFilesList !== 'undefined') {
                return this.filterdFilesList.length
            }

            return 0
        },

        // sort by dir
        sortDirection() {
            return this.sortName != 'name' ? -1 : 1
        },

        // ops
        ops_btn_disable() {
            return (this.isBulkSelecting() && !this.bulkItemsFilter.length) ||
                this.isLoading ||
                !this.selectedFile ||
                this.IsLocked(this.selectedFile)
        },
        editor_btn_disable() {
            return this.ops_btn_disable ||
                !this.selectedFileIs('image') ||
                this.selectedFile.type.includes('gif')
        },
        lock_btn_disable() {
            return this.searchItemsCount == 0 ||
                this.isLoading ||
                !this.allItemsCount ||
                this.isBulkSelecting() && !this.bulkItemsCount
        },
        vis_btn_disable() {
            return this.searchItemsCount == 0 ||
                this.isLoading ||
                !this.allItemsCount ||
                !this.isBulkSelecting() && this.selectedFileIs('folder') ||
                this.isBulkSelecting() && !this.bulkItemsCount
        }
    }
}
