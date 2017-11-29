export default {
    methods: {
        /*                Main                */
        getFiles(folders = '/', select_prev = null) {

            this.toggleLoading()
            this.toggleInfoPanel()
            this.noFiles('hide')
            if (!this.file_loader) {
                this.loadingFiles('show')
            }
            this.resetInput(['searchFor', 'sortBy', 'currentFilterName', 'selectedFile'])

            if (folders !== '/') {
                folders = '/' + folders.join('/')
            }

            // files list
            axios.post(this.filesRoute, {
                folder: folders
            }).then(({data}) => {

                this.toggleLoading()

                // folder doesnt exist
                if (data.error) {
                    if (this.checkForRestrictedPath()) {
                        EventHub.fire('get-folders', false)
                    }

                    return this.showNotif(data.error, 'danger')
                }

                // check for restricted path
                if (this.checkForRestrictedPath()) {
                    EventHub.fire('get-folders', true)
                }

                this.files = data.files
                this.lockedList = data.locked

                // check for prev opened folder
                if (select_prev) {
                    this.$nextTick(() => {
                        this.files.items.map((e, i) => {
                            if (e.name == select_prev) {
                                return this.setSelected(e, i)
                            }
                        })
                    })
                } else if (this.allItemsCount) {
                    this.selectFirst()
                }

                // check for hidden extensions
                if (this.hideExt.length) {
                    this.files.items = this.files.items.filter((e) => {
                        return !this.checkForRestrictedExt(e)
                    })
                }

                this.loadingFiles('hide')
                this.toggleInfoPanel()
                this.updateDirsList()

            }).catch(() => {
                this.ajaxError()
            })
        },
        updateDirsList() {
            axios.post(this.dirsRoute, {
                folder_location: this.folders
            }).then(({data}) => {
                // nested folders
                if (this.lockedList.length && this.files.path !== '') {
                    return this.directories = data.filter((e) => {
                        return !this.lockedList.includes(`${this.baseUrl}${this.folders.join('/')}${e}`)
                    })
                }

                // root
                if (this.lockedList.length) {
                    return this.directories = data.filter((e) => {
                        return !this.lockedList.includes(`${this.baseUrl}${e}`)
                    })
                }

                this.directories = data

            }).catch(() => {
                this.ajaxError()
            })
        },

        /*                Tool-Bar                */
        NewFolderForm(event) {
            let folder_name = this.new_folder_name

            if (!folder_name) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            if (folder_name.match(/^.\/.*|^.$/)) {
                return this.showNotif(this.trans('single_char_folder'), 'danger')
            }

            this.toggleLoading()

            axios.post(event.target.action, {
                current_path: this.files.path,
                new_folder_name: folder_name
            }).then(({data}) => {
                this.toggleLoading()
                this.resetInput('new_folder_name')
                this.toggleModal()

                if (!data.success) {
                    return this.showNotif(data.message, 'danger')
                }

                this.showNotif(`Successfully Created "${data.new_folder_name}" at "${data.full_path}"`)
                this.getFiles(this.folders)

            }).catch(() => {
                this.ajaxError()
            })
        },

        // rename
        RenameFileForm(event) {
            let changed = this.new_filename

            if (!changed) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            this.toggleLoading()

            let filename = this.selectedFile.name
            let ext = this.getExtension(filename)
            let new_filename = ext == null ? changed : `${changed}.${ext}`

            axios.post(event.target.action, {
                folder_location: this.files.path,
                filename: filename,
                new_filename: new_filename
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()

                if (!data.success) {
                    return this.showNotif(data.message, 'danger')
                }

                this.showNotif(`Successfully Renamed "${filename}" to "${data.new_filename}"`)
                this.updateItemName(this.selectedFile, filename, data.new_filename)

                if (this.selectedFileIs('folder')) {
                    this.updateDirsList()
                }

            }).catch(() => {
                this.ajaxError()
            })
        },

        // move
        MoveFileForm(event) {
            if (this.checkForFolders) {
                if (this.bulkItemsCount) {
                    this.move_file(this.bulkListFilter, event.target.action)

                    setTimeout(() => {
                        this.blkSlct()
                    }, 100)
                } else {
                    this.move_file([this.selectedFile], event.target.action)
                }
            }
        },
        move_file(files, routeUrl) {
            this.toggleLoading()

            let destination = this.moveToPath

            axios.post(routeUrl, {
                folder_location: this.files.path,
                destination: destination,
                moved_files: files
            }).then(({data}) => {
                this.toggleLoading()

                data.data.map((item) => {
                    if (!item.success) {
                        return this.showNotif(item.message, 'danger')
                    }

                    this.showNotif(`Successfully moved "${item.name}" to "${destination}"`)
                    this.removeFromLists(item.name, item.type)

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
                this.selectFirst()
                if (this.searchFor) {
                    this.searchItemsCount = this.filesList.length
                }

            }).catch(() => {
                this.ajaxError()
            })
        },

        // delete
        DeleteFileForm(event) {
            if (this.bulkItemsCount) {
                this.delete_file(this.bulkListFilter, event.target.action)

                setTimeout(() => {
                    this.blkSlct()
                }, 100)
            } else {
                this.delete_file([this.selectedFile], event.target.action)
            }
        },
        delete_file(files, routeUrl) {
            this.toggleLoading()

            axios.post(routeUrl, {
                folder_location: this.files.path,
                deleted_files: files
            }).then(({data}) => {
                this.toggleLoading()

                data.data.map((item) => {
                    if (!item.success) {
                        return this.showNotif(item.message, 'danger')
                    }

                    this.showNotif(`Successfully Deleted "${item.name}"`, 'warning')
                    this.removeFromLists(item.name, item.type)
                })

                this.toggleModal()
                this.selectFirst()
                if (this.searchFor) {
                    this.searchItemsCount = this.filesList.length
                }

            }).catch(() => {
                this.ajaxError()
            })
        },

        // lock / unlock
        lockForm(path, state) {
            axios.post(this.lockFileRoute, {
                path: path,
                state: state
            }).then(({data}) => {
                this.showNotif(data.message)
            }).catch(() => {
                this.ajaxError()
            })
        },

        /*                Ops                */
        removeFromLists(name, type) {
            if (this.filteredItemsCount) {
                let list = this.filterdList

                list.map((e) => {
                    if (e.name.includes(name) && e.type.includes(type)) {
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
                if (e.name.includes(name) && e.type.includes(type)) {
                    let list = this.files.items

                    list.splice(list.indexOf(e), 1)
                }
            })

            this.resetInput('selectedFile')
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
        }
    }
}
