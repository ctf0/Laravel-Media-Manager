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
                this.files = res
                this.loadingFiles('hide')
                this.selectFirst()
                $('#right').fadeIn()

            }).fail(() => {
                this.ajaxError()
            })

            // dirs list
            this.updateDirsList()
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
            $.post(event.target.action, {
                current_path: this.files.path,
                new_folder_name: this.new_folder_name
            }, (data) => {
                if (data.success) {
                    this.showNotif({
                        title: 'Success',
                        body: `Successfully Created "${data.new_folder_name}" at "${data.full_path}"`,
                        type: 'success',
                        duration: 3
                    })

                    this.getFiles(this.folders)
                } else {
                    this.showNotif({
                        title: 'Error',
                        body: data.message,
                        type: 'danger'
                    })
                }

                this.resetInput('new_folder_name')
                this.toggleModal()
                this.scrollToFile()

            }).fail(() => {
                this.ajaxError()
            })
        },
        RenameFileForm(event) {
            let filename = this.selectedFile.name
            let changed = this.new_filename

            let ext = filename.lastIndexOf('.') > 0 ? filename.substring(filename.lastIndexOf('.') + 1) : null
            let new_filename = ext == null ? changed : `${changed}.${ext}`

            $.post(event.target.action, {
                folder_location: this.folders,
                filename: filename,
                new_filename: new_filename
            }, (data) => {
                if (data.success) {
                    this.showNotif({
                        title: 'Success',
                        body: `Successfully Renamed "${filename}" to "${data.new_filename}"`,
                        type: 'success',
                        duration: 3
                    })

                    this.updateItemName(this.selectedFile, filename, data.new_filename)

                    if (this.selectedFileIs('folder')) {
                        this.updateDirsList()
                    }
                } else {
                    this.showNotif({
                        title: 'Error',
                        body: data.message,
                        type: 'danger'
                    })
                }

                this.toggleModal()

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
            let destination = $('#move_folder_dropdown').val()

            $.post(routeUrl, {
                folder_location: this.folders,
                destination: destination,
                moved_files: files
            }, (res) => {
                res.data.map((item) => {
                    if (item.success) {
                        this.showNotif({
                            title: 'Success',
                            body: `Successfully moved "${item.name}" to "${destination}"`,
                            type: 'success',
                            duration: 3
                        })

                        this.removeFromLists(item.name)
                        this.updateFolderCount(destination, 1, item.size)

                        // update dirs list after move
                        if (item.type.includes('folder')) {
                            this.updateDirsList()
                        }
                    } else {
                        this.showNotif({
                            title: 'Error',
                            body: item.message,
                            type: 'danger'
                        })
                    }
                })

                // update folder count when folder is moved into another
                files.map((e) => {
                    if (e.items && e.items > 0) {
                        this.updateFolderCount(destination, e.items)
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
            $.post(routeUrl, {
                folder_location: this.folders,
                deleted_files: files
            }, (res) => {
                res.data.map((item) => {
                    if (item.success) {
                        this.showNotif({
                            title: 'Success',
                            body: `Successfully Deleted "${item.name}"`,
                            type: 'warning',
                            duration: 3
                        })

                        this.removeFromLists(item.name)
                    } else {
                        this.showNotif({
                            title: 'Error',
                            body: item.message,
                            type: 'danger'
                        })
                    }
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
