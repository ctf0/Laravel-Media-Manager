export default {
    watch: {
        allFiles(val) {
            if (val.length < 1) {
                this.noFiles('show')
            }
        },
        selectedFile(val) {
            if (val) {
                if (this.inModal && !this.selectedFileIs('folder')) {
                    EventHub.fire('file_selected', val.path)
                }

                if (this.checkForFolders) {
                    this.$nextTick(() => {
                        let item = this.$refs.move_folder_dropdown.options[0]
                        if (item) {
                            return this.moveToPath = item.value
                        }
                    })
                }
            }
        },
        checkForFolders(val) {
            if (!val) {
                this.resetInput('moveToPath')
            }
        },
        bulkItemsCount(val) {
            if (val > 1 && !this.bulkSelectAll) {
                this.bulkSelectAll = true
            }
        },
        active_modal(val) {
            if (val == 'new_folder_modal') {
                this.$nextTick(() => {
                    return this.$refs.new_folder_modal_input.focus()
                })
            }

            if (val == 'rename_file_modal') {
                this.$nextTick(() => {
                    return this.$refs.rename_file_modal_input.focus()
                })
            }

            if (val == 'move_file_modal') {
                this.$nextTick(() => {
                    return this.$refs.move_folder_dropdown.focus()
                })
            }

            if (val == 'confirm_delete_modal') {
                this.$nextTick(() => {
                    return this.$refs.confirm_delete_modal_submit.focus()
                })
            }
        },
        randomNames(val) {
            return this.$ls.set('mm-uploadRndNames', val)
        },

        // filter
        sortBy(val) {
            if (val) {
                if (val == 'clear') {
                    this.resetInput('sortBy')
                }

                if (!this.isBulkSelecting()) {
                    this.selectFirst()
                }
            }
        },
        currentFilterName(val) {
            if (!val) {
                this.resetInput('filterdList', [])
            }
        },

        // search
        searchFor(val) {
            if (!this.isBulkSelecting()) {
                this.selectFirst()
            }

            if (!val) {
                this.resetInput('searchItemsCount')
                return this.noFiles('hide')
            }
        },
        searchItemsCount(val) {
            if (val == 0) {
                this.resetInput('selectedFile')
            }

            if (this.allItemsCount == undefined) {
                this.resetInput('searchItemsCount')
            }
        }
    }
}
