import Download from './download'

export default {
    mixins: [Download],
    methods: {
        /*                Main                */
        getFiles(folders = '/', prev_folder = null, prev_file = null) {

            this.noFiles('hide')

            if (!this.loading_files) {
                this.toggleInfo = false
                this.toggleLoading()
                this.loadingFiles('show')
            }

            this.resetInput(['searchFor', 'sortBy', 'currentFilterName', 'selectedFile', 'currentFileIndex'])

            if (folders !== '/') {
                folders = '/' + folders.join('/')
            }

            // files list
            axios.post(this.filesRoute, {
                folder: folders
            }).then(({data}) => {

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

                // check for hidden extensions
                if (this.hideExt.length) {
                    this.files.items = this.files.items.filter((e) => {
                        return !this.checkForHiddenExt(e)
                    })
                }

                // check for hidden folders
                if (this.hidePath.length) {
                    this.files.items = this.files.items.filter((e) => {
                        return !this.checkForHiddenPath(e)
                    })
                }

                // we have files
                if (this.allItemsCount) {
                    this.selectFirst()
                }

                this.$nextTick(() => {
                    // check for prev opened folder
                    if (prev_folder) {
                        this.files.items.some((e, i) => {
                            if (e.name == prev_folder) {
                                return this.setSelected(e, i)
                            }
                        })
                    }

                    // check for prev selected file
                    if (prev_file) {
                        this.files.items.some((e, i) => {
                            if (e.name == prev_file) {
                                return this.setSelected(e, i)
                            }
                        })
                    }
                })

                // we have files
                if (this.allItemsCount) {
                    this.toggleLoading()
                    this.loadingFiles('hide')
                    this.toggleInfo = true

                    this.$nextTick(() => {
                        // scroll to prev selected item
                        if (this.currentFileIndex) {
                            this.scrollToFile(this.$refs[`file_${this.currentFileIndex}`])
                        }

                        // scroll to breadcrumb item
                        let name = folders.split('/').pop()
                        let count = document.getElementById(`${name ? name : 'library'}-bc`).offsetLeft
                        this.$refs.bc.$el.scrollBy({top: 0, left: count, behavior: 'smooth'})
                    })

                    return this.updateDirsList()
                }

                // we dont have files
                this.toggleLoading()
                this.loadingFiles('hide')

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },
        updateDirsList() {
            axios.post(this.dirsRoute, {
                folder_location: this.folders
            }).then(({data}) => {

                this.directories = data

                // check for hidden folders
                if (this.hidePath.length) {
                    this.directories = this.directories.filter((e) => {
                        return !this.checkForFolderName(e)
                    })
                }

                if (this.lockedList.length) {
                    // nested folders
                    if (this.files.path !== '') {
                        return this.directories = this.directories.filter((e) => {
                            return !this.lockedList.includes(`${this.baseUrl}${this.folders.join('/')}${e}`)
                        })
                    }

                    // root
                    this.directories = this.directories.filter((e) => {
                        return !this.lockedList.includes(`${this.baseUrl}${e}`)
                    })
                }

            }).catch((err) => {
                console.error(err)
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

                this.showNotif(`${this.trans('create_success')} "${data.new_folder_name}" at "${data.full_path}"`)
                this.getFiles(this.folders, data.new_folder_name)

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        // rename
        RenameFileForm(event) {
            let changed = this.new_filename

            if (!changed) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            if (this.selectedFileIs('folder') && changed.match(/^.\/.*|^.$/)) {
                return this.showNotif(this.trans('single_char_folder'), 'danger')
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

                this.showNotif(`${this.trans('rename_success')} "${filename}" to "${data.new_filename}"`)
                this.updateItemName(this.selectedFile, filename, data.new_filename)

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
                let list = this.bulkItemsCount
                    ? this.bulkListFilter
                    : [this.selectedFile]

                this.move_file(list, event.target.action)
            }
        },
        move_file(files, routeUrl) {
            this.toggleLoading()

            let destination = this.moveToPath
            let copy = this.useCopy
            let error = false

            axios.post(routeUrl, {
                folder_location: this.files.path,
                destination: destination,
                moved_files: files,
                use_copy: copy
            }).then(({data}) => {
                this.toggleLoading()

                data.data.map((item) => {
                    if (!item.success) {
                        error = true
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

                this.$refs['success-audio'].play()
                this.toggleModal()

                this.isBulkSelecting()
                    ? this.blkSlct()
                    : error ? false : this.selectFirst()

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        // delete
        DeleteFileForm(event) {
            let list = this.bulkItemsCount
                ? this.bulkListFilter
                : [this.selectedFile]

            this.delete_file(list, event.target.action)
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

                    this.showNotif(`${this.trans('delete_success')} "${item.name}"`, 'warning')
                    this.removeFromLists(item.name, item.type)
                })

                this.$refs['success-audio'].play()
                this.toggleModal()
                this.isBulkSelecting() ? this.blkSlct() : this.selectFirst()
                if (this.searchFor) {
                    this.searchItemsCount = this.filesList.length
                }

            }).catch((err) => {
                console.error(err)
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
            }).catch((err) => {
                console.error(err)
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

            this.resetInput(['selectedFile', 'currentFileIndex'])
        },
        updateFolderCount(destination, count, weight = 0) {
            if (destination !== '../') {

                if (destination.includes('/')) {
                    // get the first dir in the path
                    // because this is what the user is currently viewing
                    destination = destination.split('/')
                    destination = destination[0] == '' ? destination[1] : destination[0]
                }

                if (this.filteredItemsCount) {
                    this.filterdList.some((e) => {
                        if (e.type == 'folder' && e.name == destination) {
                            e.items += parseInt(count)
                            e.size += parseInt(weight)
                        }
                    })
                }

                this.files.items.some((e) => {
                    if (e.type == 'folder' && e.name == destination) {
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
