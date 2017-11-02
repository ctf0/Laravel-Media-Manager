export default {
    methods: {
        /*                Main                */
        getFiles(folders) {
            this.loadingFiles('show')
            this.resetInput('searchFor')
            this.resetInput('showBy')
            this.showFilesOfType('all')

            let folder_location = '/'

            if (folders !== '/') {
                folder_location = '/' + folders.join('/')
            }

            // files list
            $.post(this.filesRoute, {
                folder: folder_location
            }, (res) => {
                this.loadingFiles('hide')

                if (res.error) {
                    if (this.checkForRestrictedPath()) {
                        EventHub.fire('get-folders', false)
                    }

                    this.noFiles('show')
                    return this.showNotif(res.error, 'danger')
                }

                if (this.checkForRestrictedPath()) {
                    EventHub.fire('get-folders', true)
                }

                this.files = res

                if (this.restrictExt.length) {
                    this.files.items = this.files.items.filter((e) => {
                        return !this.checkForRestrictedExt(e)
                    })
                }

                this.noFiles('hide')
                this.selectFirst()
                $('#right').fadeIn()

                // dirs list
                this.updateDirsList()
            }).fail(() => {
                this.ajaxError()
            })
        },
        updateDirsList() {
            $.post(this.dirsRoute, {
                folder_location: this.folders
            }, (data) => {
                this.directories = data

            }).fail(() => {
                this.ajaxError()
            })
        },

        /*                Tool-Bar                */
        NewFolderForm(event) {
            let folder_name = this.new_folder_name

            if (!folder_name) {
                return this.showNotif('Maybe You Should Add Something First', 'warning')
            }

            this.toggleLoading()

            $.post(event.target.action, {
                current_path: this.files.path,
                new_folder_name: folder_name
            }, (data) => {
                this.toggleLoading()
                this.resetInput('new_folder_name')
                this.toggleModal()

                if (!data.success) {
                    return this.showNotif(data.message, 'danger')
                }

                this.showNotif(`Successfully Created "${data.new_folder_name}" at "${data.full_path}"`)
                this.getFiles(this.folders)

            }).fail(() => {
                this.ajaxError()
            })
        },
        RenameFileForm(event) {
            let changed = this.new_filename

            if (!changed) {
                return this.showNotif('Maybe You Should Add Something First', 'warning')
            }

            this.toggleLoading()

            let filename = this.selectedFile.name

            let ext = this.getExtension(filename)
            let new_filename = ext == null ? changed : `${changed}.${ext}`

            $.post(event.target.action, {
                folder_location: this.folders,
                filename: filename,
                new_filename: new_filename
            }, (data) => {
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

            }).fail(() => {
                this.ajaxError()
            })
        },
        MoveFileForm(event) {
            if ($('#move_folder_dropdown').val() !== null) {
                if (this.bulkItemsCount) {
                    this.move_file(this.bulkList, event.target.action)
                    setTimeout(() => {
                        $('#blk_slct').trigger('click')
                    }, 100)
                } else {
                    this.move_file([this.selectedFile], event.target.action)
                }
            }
        },
        DeleteFileForm(event) {
            if (this.bulkItemsCount) {
                this.delete_file(this.bulkList, event.target.action)
                setTimeout(() => {
                    $('#blk_slct').trigger('click')
                }, 100)
            } else {
                this.delete_file([this.selectedFile], event.target.action)
            }
        },

        /*                Ops                */
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
            if (this.filterdList.length) {
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

                if (this.filterdList.length) {
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
