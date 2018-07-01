import debounce from 'lodash/debounce'

export default {
    watch: {
        allFiles(val) {
            if (val.length < 1) {
                this.noFiles('show')
            }
        },
        selectedFile(val) {
            if (val) {
                if (this.inModal && !this.isBulkSelecting()) {
                    this.selectedFileIs('folder')
                        ? EventHub.fire('folder_selected', `${this.files.path}/${val.name}`)
                        : EventHub.fire('file_selected', val.path)
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
            !val
                ? this.resetInput('moveToPath')
                : this.moveToPath = this.$refs.move_folder_dropdown.options[0].value
        },
        allItemsCount(val) {
            if (val && val.length == 0) {
                this.resetInput(['selectedFile', 'currentFileIndex'])
            }
        },
        bulkItemsCount(val) {
            if (val > 0 && this.inModal && !this.selectedFileIs('folder')) {
                let links = this.bulkList.map((e) => e.path)
                EventHub.fire('multi_file_selected', links)
            }

            if (val > 1 && !this.bulkSelectAll) {
                this.bulkSelectAll = true
            }
        },
        bulkSelect(val) {
            this.toggleUploadArea = false

            if (!val) {
                this.firstMeta = false
            }
        },
        activeModal(val) {
            let ref

            switch (val) {
                case 'new_folder_modal':
                    ref = 'new_folder_modal_input'
                    break
                case 'rename_file_modal':
                    ref = 'rename_file_modal_input'
                    break
                case 'move_file_modal':
                    ref = 'move_folder_dropdown'
                    break
                case 'confirm_delete_modal':
                    ref = 'confirm_delete_modal_submit'
                    break
                case 'save_link_modal':
                    ref = 'save_link_modal_input'
                    break
                default:
                    ref = null
            }

            if (ref) {
                this.$nextTick(() => {
                    return this.$refs[ref].focus()
                })
            }
        },
        showProgress(val) {
            if (val) {
                this.toggleUploadArea = false
                this.toggleInfo = false
                this.isLoading = true
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
        lockedList(val) {
            this.updateLs({'lockedList': val})
        },

        // filter
        sortBy(val) {
            if (val) {
                if (val == 'clear') {
                    this.resetInput('sortBy')
                }

                if (!this.isBulkSelecting()) {
                    !this.lazyModeIsOn()
                        ? this.selectFirst()
                        : this.lazySelectFirst()
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
                !this.lazyModeIsOn()
                    ? this.selectFirst()
                    : this.lazySelectFirst()
            }

            if (val) {
                return this.updateSearchCount()
            }

            this.resetInput('searchItemsCount')
            this.noSearch('hide')
            this.selectFirstInBulkList()
        },
        searchItemsCount(val) {
            val == 0
                ? this.resetInput(['selectedFile', 'currentFileIndex'])
                : this.selectFirstInBulkList()

            if (this.allItemsCount == undefined || val == this.allItemsCount) {
                this.resetInput('searchItemsCount')
            }
        },
        toggleInfo() {
            if (!this.firstRun && this.currentFileIndex) {
                this.$nextTick(debounce(() => {
                    this.scrollByRow()
                    this.scrollToSelected(this.getElementByIndex(this.currentFileIndex))
                }, 500))
            }
        }
    }
}
