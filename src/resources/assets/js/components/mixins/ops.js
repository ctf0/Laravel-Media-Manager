export default {
    methods: {
        /*                Forms                */
        move_file(files, routeUrl) {
            this.toggleLoading()

            let destination = $('#move_folder_dropdown').val()

            $.post(routeUrl, {
                folder_location: this.folders,
                destination: destination,
                moved_files: files
            }, (res) => {
                this.toggleLoading()

                res.data.map((item) => {
                    if (!item.success) {
                        return this.showNotif(item.message, 'danger')
                    }

                    this.showNotif(`Successfully moved "${item.name}" to "${destination}"`)
                    this.removeFromLists(item.name)

                    // update folder count when folder is moved into another
                    if (this.fileTypeIs(item, 'folder')) {
                        if (item.items > 0) {
                            this.updateFolderCount(destination, item.items, item.size)
                        }

                        // update dirs list after move
                        this.updateDirsList()
                    } else {
                        this.updateFolderCount(destination, 1, item.size)
                    }
                })

                this.toggleModal()
                this.updateFoundCount(files.length)
                this.selectFirst()

            }).fail(() => {
                this.ajaxError()
            })
        },
        delete_file(files, routeUrl) {
            this.toggleLoading()

            $.post(routeUrl, {
                folder_location: this.folders,
                deleted_files: files
            }, (res) => {
                this.toggleLoading()

                res.data.map((item) => {
                    if (!item.success) {
                        return this.showNotif(item.message, 'danger')
                    }

                    this.showNotif(`Successfully Deleted "${item.name}"`, 'warning')
                    this.removeFromLists(item.name)
                })

                this.toggleModal()
                this.updateFoundCount(files.length)
                this.selectFirst()

            }).fail(() => {
                this.ajaxError()
            })
        },
        removeFromLists(name) {
            if (this.filteredItemsCount) {
                let list = this.filterdList

                list.map((e) => {
                    if (e.name.includes(name)) {
                        list.splice(list.indexOf(e), 1)
                    }
                })
            }

            if (this.directories.length) {
                let list = this.directories

                list.map((e) => {
                    if (e.includes(name)) {
                        list.splice(list.indexOf(e), 1)
                    }
                })
            }

            this.files.items.map((e) => {
                if (e.name.includes(name)) {
                    let list = this.files.items

                    list.splice(list.indexOf(e), 1)
                }
            })

            this.clearSelected()
        },
        updateFolderCount(destination, count, weight = 0) {
            if (destination !== '../') {

                if (destination.includes('/')) {
                    destination = destination.split('/').shift()
                }

                if (this.filteredItemsCount) {
                    this.filterdList.map((e) => {
                        if (e.name.includes(destination)) {
                            e.items += parseInt(count)
                            e.size += parseInt(weight)
                        }
                    })
                }

                this.files.items.some((e) => {
                    if (e.name.includes(destination)) {
                        e.items += parseInt(count)
                        e.size += parseInt(weight)
                    }
                })
            }
        },
        updateItemName(item, oldName, newName) {
            // update the main files list
            let filesIndex = this.files.items[this.files.items.indexOf(item)]
            filesIndex.name = newName
            filesIndex.path = filesIndex.path.replace(oldName, newName)

            // if found in the filterd list, then update it aswell
            if (this.filterdList.includes(item)) {
                let filterIndex = this.filterdList[this.filterdList.indexOf(item)]
                filterIndex.name = newName
                filesIndex.path = filterIndex.path.replace(oldName, newName)
            }
        },

        /*                Buttons                */
        del() {
            if (!this.isBulkSelecting() && this.selectedFile) {
                if (this.selectedFileIs('folder')) {
                    this.folderWarning = true
                } else {
                    this.folderWarning = false
                }

                this.$refs.confirm_delete.innerText = this.selectedFile.name
            }

            if (this.bulkItemsCount) {
                this.bulkListFilter.some((item) => {
                    if (this.fileTypeIs(item, 'folder')) {
                        return this.folderWarning = true
                    }

                    this.folderWarning = false
                })
            }
        },

        /*                Touch                */
        deleteItem() {
            this.$refs.delete.click()
        },
        moveItem() {
            this.$refs.move.click()
        },
        renameItem() {
            this.$refs.rename.click()
        }
    }
}
