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

                return this.updateLs({'selectedFileName': val.name})
            }

            this.updateLs({'selectedFileName': null})
        },
        checkForFolders(val) {
            if (!val) {
                this.resetInput('moveToPath')
            } else {
                this.moveToPath = this.$refs.move_folder_dropdown.options[0].value
            }
        },
        bulkItemsCount(val) {
            if (val > 1 && !this.bulkSelectAll) {
                this.bulkSelectAll = true
            }
        },
        activeModal(val) {
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

            if (val == 'save_link_modal') {
                this.$nextTick(() => {
                    return this.$refs.save_link_modal_input.focus()
                })
            }
        },
        showProgress(val) {
            if (val) {
                this.toggleUploadArea = false
                this.toggleInfo = false
                this.toggleLoading()
                this.noFiles('hide')
                this.loadingFiles('show')
            }
        },

        // ls
        randomNames(val) {
            this.updateLs({'randomNames': val})
        },
        folders(val) {
            this.updateLs({'folders': val})
        },
        toolBar(val) {
            this.updateLs({'toolBar': val})
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
                this.noSearch('hide')

                this.selectFirstInBulkList()
            }
        },
        searchItemsCount(val) {
            if (val == 0) {
                this.resetInput(['selectedFile', 'currentFileIndex'])
            } else {
                this.selectFirstInBulkList()
            }

            if (this.allItemsCount == undefined || val == this.allItemsCount) {
                this.resetInput('searchItemsCount')
            }
        }
    }
}
