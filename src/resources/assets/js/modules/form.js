export default {
    methods: {
        /*                Main                */
        getFiles(folders = null, prev_folder = null, prev_file = null) {
            this.resetInput(['sortBy', 'currentFilterName', 'selectedFile', 'currentFileIndex'])
            this.noFiles('hide')
            this.destroyPlyr()

            if (!this.loading_files) {
                this.isLoading = true
                this.infoSidebar = false
                this.loadingFiles('show')
            }

            if (folders) {
                folders = this.clearDblSlash(`/${folders.join('/')}`)
            }

            // get data
            return axios.post(this.routes.files, {
                folder: folders,
                dirs: this.folders
            }).then(({data}) => {
                // folder doesnt exist
                if (data.error) {
                    return this.showNotif(data.error, 'danger')
                }

                // return data
                this.files = data.files
                this.lockedList = data.locked
                this.directories = data.dirs
                this.filesListCheck(folders, prev_folder, prev_file)

            }).catch((err) => {
                console.error(err)

                this.isLoading = false
                this.loadingFiles('hide')
                this.ajaxError()
            })
        },
        updateDirsList() {
            axios.post(this.routes.dirs, {
                folder_location: this.folders
            }).then(({data}) => {
                this.dirsListCheck(data)
            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        filesListCheck(folders, prev_folder, prev_file) {
            let files = this.files.items

            if (this.hideExt.length) {
                files = files.filter((e) => !this.checkForHiddenExt(e))
            }

            if (this.hidePath.length) {
                files = files.filter((e) => !this.checkForHiddenPath(e))
            }

            if (this.restrictModeIsOn()) {
                files = files.filter((e) => e.type != 'folder')
            }

            this.files.items = files

            // we have files
            if (this.allItemsCount) {
                // check for prev
                if (prev_file || prev_folder) {
                    let index = null

                    files.some((e, i) => {
                        if (prev_file && e.name == prev_file) {
                            index = i
                        }

                        if (prev_folder && e.name == prev_folder) {
                            index = i
                        }

                        return this.currentFileIndex = index
                    })
                }

                this.$nextTick(() => {
                    // no prev found
                    if (!this.currentFileIndex) {
                        this.selectFirst()
                    }

                    // update search
                    if (this.searchFor) {
                        this.updateSearchCount()
                    }

                    // update dirs
                    this.dirsListCheck()
                })
            }

            this.isLoading = false
            this.loadingFiles('hide')
            this.smallScreenHelper()

            // we dont have files & user clicked the "refresh btn"
            this.$nextTick(() => {
                if (!this.allItemsCount && !this.no_files) {
                    this.noFiles('show')
                }

                EventHub.fire('start-img-observing')
            })
        },

        /*                Tool-Bar                */
        NewFolderForm(event) {
            let folder_name = this.newFolderName
            let path = this.files.path

            if (!folder_name) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            this.toggleLoading()

            axios.post(event.target.action, {
                path: path,
                new_folder_name: folder_name
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()
                this.resetInput('newFolderName')

                if (data.message) {
                    return this.showNotif(data.message, 'danger')
                }

                this.showNotif(`${this.trans('create_success')} "${data.new_folder_name}" at "${path || '/'}"`)
                this.isBulkSelecting() ? this.blkSlct() : false
                this.getFiles(this.folders, data.new_folder_name)

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        // rename
        RenameFileForm(event) {
            let selected = this.selectedFile
            let changed = this.newFilename
            let filename = selected.name
            let ext = this.getExtension(filename)
            let newFilename = ext == null ? changed : `${changed}.${ext}`

            if (!changed) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            if (this.selectedFileIs('folder') && this.hasLockedItems(filename, this.getCacheName(filename))) {
                this.showNotif(`"${filename}" ${this.trans('error_altered_fwli')}`, 'danger')

                return this.toggleModal()
            }

            this.toggleLoading()

            axios.post(event.target.action, {
                path: this.files.path,
                filename: filename,
                new_filename: newFilename,
                type: selected.type
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()

                if (data.message) {
                    return this.showNotif(data.message, 'danger')
                }

                let savedName = data.new_filename

                this.showNotif(`${this.trans('rename_success')} "${filename}" to "${savedName}"`)
                selected.name = savedName
                selected.path = selected.path.replace(filename, savedName)

                // update folders
                if (this.selectedFileIs('folder')) {
                    this.updateDirsList()
                }

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        // move
        MoveFileForm(event) {
            if (this.checkForFolders) {
                let hasErrors = false
                let destination = this.moveToPath
                let copy = this.useCopy
                let files = this.checkNestedLockedItems(
                    this.bulkItemsCount
                        ? this.bulkItemsFilter
                        : [this.selectedFile]
                )

                if (!files.length) {
                    return this.toggleModal()
                }

                this.toggleLoading()

                axios.post(event.target.action, {
                    path: this.files.path,
                    destination: destination,
                    moved_files: files,
                    use_copy: copy
                }).then(({data}) => {
                    this.toggleLoading()
                    this.toggleModal()

                    data.map((item) => {
                        if (!item.success) {
                            hasErrors = true

                            return this.showNotif(item.message, 'danger')
                        }

                        // copy
                        if (copy) {
                            this.showNotif(`${this.trans('copy_success')} "${item.name}" to "${destination}"`)
                        }

                        // move
                        else {
                            this.showNotif(`${this.trans('move_success')} "${item.name}" to "${destination}"`)
                            this.removeFromLists(item.name, item.type)

                            // update dirs list after move
                            this.updateDirsList()

                            // update search count
                            if (this.searchFor) {
                                this.searchItemsCount = this.filesList.length
                            }
                        }

                        // update folder count when folder is moved/copied into another
                        this.fileTypeIs(item, 'folder')
                            ? this.updateFolderCount(destination, item.items, item.size)
                            : this.updateFolderCount(destination, 1, item.size)
                    })

                    if (this.allItemsCount) {
                        this.isBulkSelecting()
                            ? this.blkSlct()
                            : hasErrors
                                ? false
                                : this.selectFirst()
                    }
                }).catch((err) => {
                    console.error(err)
                    this.ajaxError()
                })
            }
        },

        // delete
        DeleteFileForm(event) {
            let files = this.checkNestedLockedItems(
                this.bulkItemsCount
                    ? this.bulkItemsFilter
                    : [this.selectedFile]
            )

            if (!files.length) {
                return this.toggleModal()
            }

            this.toggleLoading()

            axios.post(event.target.action, {
                path: this.files.path,
                deleted_files: files
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()

                data.map((item) => {
                    if (!item.success) {
                        return this.showNotif(item.message, 'danger')
                    }

                    this.showNotif(`${this.trans('delete_success')} "${item.name}"`)
                    this.removeFromLists(item.name, item.type)
                })

                this.isBulkSelecting()
                    ? this.blkSlct()
                    : this.allItemsCount
                        ? this.selectFirst()
                        : false

                this.$nextTick(() => {
                    if (this.searchFor) {
                        this.searchItemsCount = this.filesList.length
                    }
                })

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        /*                Ops                */
        removeFromLists(name, type, reset = true) {
            if (type == 'folder' && this.directories.length) {
                this.updateListsRemove(this.directories, name, type)
            }

            if (this.filteredItemsCount) {
                this.updateListsRemove(this.filterdList, name, type)
            }

            this.updateListsRemove(this.files.items, name, type)

            if (reset) this.resetInput(['selectedFile', 'currentFileIndex'])
        },
        updateListsRemove(list, name, type) {
            return list.some((e) => {
                if (e.name == name && e.type == type) {
                    list.splice(list.indexOf(e), 1)
                }
            })
        },
        updateFolderCount(destination, count, weight = 0) {
            if (destination !== '../') {

                if (destination.includes('/')) {
                    // get the first dir in the path
                    // because this is what the user is currently viewing
                    destination = this.arrayFilter(destination.split('/'))
                    destination = destination[0]
                }

                if (this.filteredItemsCount) {
                    this.filterdList.some((e) => {
                        if (e.type == 'folder' && e.name == destination) {
                            e.count += parseInt(count)
                            e.size += parseInt(weight)
                        }
                    })
                }

                this.files.items.some((e) => {
                    if (e.type == 'folder' && e.name == destination) {
                        e.count += parseInt(count)
                        e.size += parseInt(weight)
                    }
                })
            }
        }
    }
}
