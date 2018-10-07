export default {
    watch: {
        // files
        selectedFile(val) {
            this.scrollableBtn = {
                state: false,
                dir: 'down'
            }

            if (val) {
                if (this.inModal && !this.isBulkSelecting()) {
                    this.selectedFileIs('folder')
                        ? EventHub.fire('folder_selected', `${this.files.path}/${val.name}`)
                        : EventHub.fire('file_selected', val.path)
                }

                if (this.checkForFolders) {
                    this.$nextTick(() => {
                        this.updateMoveList()
                    })
                }

                return this.updateLs({'selectedFileName': val.name})
            }

            this.updateLs({'selectedFileName': null})
        },
        allItemsCount(val) {
            if (!val) {
                this.noFiles('show')
                this.resetInput(['selectedFile', 'currentFileIndex'])
            }
        },
        filteredItemsCount(val) {
            if (!val) {
                this.resetInput('currentFilterName')
            }
        },

        // bulk
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
            this.UploadArea = false

            if (!val) {
                this.firstMeta = false
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
                    this.lazyModeIsOn()
                        ? this.lazySelectFirst()
                        : this.selectFirst()
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
                this.lazyModeIsOn()
                    ? this.lazySelectFirst()
                    : this.selectFirst()
            }

            if (val) {
                this.updateSearchCount()
            } else {
                this.resetInput('searchItemsCount')
                this.noSearch('hide')
                this.selectFirstInBulkList()
            }

            // because the files gets removed & readded to the dom
            // which will put the images back to its init state "no-preview"
            this.$nextTick(() => {
                EventHub.fire('start-search-observing')
            })
        },
        searchItemsCount(val) {
            if (this.allItemsCount == undefined || val == this.allItemsCount) {
                this.resetInput('searchItemsCount')
            }
        },

        // progress
        showProgress(val) {
            if (val) {
                this.UploadArea = false
                this.infoSidebar = false
                this.isLoading = true
                this.noFiles('hide')
                this.loadingFiles('show')
            } else {
                this.isLoading = false
                this.loadingFiles('hide')
                this.smallScreenHelper()
            }
        },

        // misc
        infoSidebar: {
            deep: true,
            immediate: true,
            handler(val) {
                this.$nextTick(() => {
                    setTimeout(this.scrollOnLoad, 250)
                })
            }
        },
        no_files(val) {
            if (val) this.isLoading = false
        },
        checkForFolders(val) {
            val
                ? this.updateMoveList()
                : this.resetInput('moveToPath')
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
        player: 'autoPlay'
    }
}
