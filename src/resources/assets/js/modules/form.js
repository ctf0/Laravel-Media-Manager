export default {
    methods: {
        /*                Upload                */
        fileUpload() {
            const manager = this

            let items = 0
            let progress = 0
            let counter = 0
            let last = null
            let sendingComplete = false

            new Dropzone('#new-upload', {
                createImageThumbnails: false,
                parallelUploads: 10,
                hiddenInputContainer: '#new-upload',
                uploadMultiple: true,
                forceFallback: false,
                ignoreHiddenFiles: true,
                timeout: 3600000, // 60 mins
                previewsContainer: '#uploadPreview',
                addedfile() {
                    manager.showProgress = true
                    items++
                    counter = 100 / items
                },
                sending() {
                    progress += counter
                    manager.progressCounter = `${progress.toFixed(2)}%`

                    sendingComplete = parseInt(progress.toFixed(2)) == 100 ? true : false
                },
                successmultiple(files, res) {
                    res.data.map((item) => {
                        if (item.success) {
                            manager.showNotif(`${manager.trans('upload_success')} "${item.message}"`)
                            last = item.message
                        } else {
                            manager.showNotif(item.message, 'danger')
                        }
                    })

                    if (sendingComplete == true) {
                        items = 0
                        progress = 0
                        manager.progressCounter = 0
                        manager.showProgress = false

                        manager.$refs['success-audio'].play()
                        manager.removeCachedResponse('../')

                        last
                            ? manager.getFiles(manager.folders, null, last)
                            : manager.getFiles(manager.folders)
                    }
                },
                errormultiple(files, res) {
                    manager.showNotif(res, 'danger')
                }
            })
        },

        // upload image from link
        saveLinkForm(event) {
            let url = this.urlToUpload

            if (!url) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            this.toggleUploadArea = false
            this.toggleLoading()
            this.loadingFiles('show')

            axios.post(event.target.action, {
                path: this.files.path,
                url: url,
                random_names: this.randomNames
            }).then(({data}) => {
                this.toggleLoading()
                this.loadingFiles('hide')

                if (!data.success) {
                    return this.showNotif(data.message, 'danger')
                }

                this.resetInput('urlToUpload')
                this.$nextTick(() => {
                    this.$refs.save_link_modal_input.focus()
                })

                this.showNotif(`${this.trans('save_success')} "${data.message}"`)
                this.removeCachedResponse('../')
                this.getFiles(this.folders, null, data.message)

            }).catch((err) => {
                console.error(err)
                this.toggleLoading()
                this.toggleModal()
                this.loadingFiles('hide')
                this.resetInput('urlToUpload')

                this.ajaxError()
            })
        },

        /*                Main                */
        getFiles(folders = '/', prev_folder = null, prev_file = null) {

            this.resetInput(['searchFor', 'sortBy', 'currentFilterName', 'selectedFile', 'currentFileIndex'])

            this.noFiles('hide')
            if (!this.loading_files) {
                this.toggleInfo = false
                this.toggleLoading()
                this.loadingFiles('show')
            }

            if (folders !== '/') {
                folders = '/' + folders.join('/')
            }

            this.getCachedResponse()
                // get cache
                .then((res) => {
                    this.files = res.files
                    this.lockedList = res.lockedList
                    this.filesListCheck(prev_folder, prev_file, folders, res.dirs)
                })

                // or make the call if nothing found
                .catch((err) => {
                    // console.warn('localforage.getItem', err)

                    axios.post(this.filesRoute, {
                        folder: folders,
                        dirs: this.folders
                    }).then(({data}) => {

                        // folder doesnt exist
                        if (data.error) {
                            return this.showNotif(data.error, 'danger')
                        }

                        // normal
                        this.files = data.files
                        this.lockedList = data.locked
                        this.filesListCheck(prev_folder, prev_file, folders, data.dirs)

                        // cache response
                        this.cacheResponse({
                            files: data.files,
                            lockedList: data.locked,
                            dirs: data.dirs
                        })

                    }).catch((err) => {
                        console.error(err)
                        this.ajaxError()
                    })
                })
        },
        updateDirsList() {
            axios.post(this.dirsRoute, {
                folder_location: this.folders
            }).then(({data}) => {
                this.dirsListCheck(data)
            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        filesListCheck(prev_folder, prev_file, folders, dirs) {
            // check for hidden extensions
            if (this.hideExt.length) {
                this.files.items = this.files.items.filter((e) => {
                    return !this.checkForHiddenExt(e)
                })
            }

            // check for hidden folders in files
            if (this.hidePath.length) {
                this.files.items = this.files.items.filter((e) => {
                    return !this.checkForHiddenPath(e)
                })
            }

            // we have files
            if (this.allItemsCount) {
                this.toggleLoading()
                this.loadingFiles('hide')
                this.toggleInfo = true

                this.$nextTick(() => {
                    // lazy loading is not active
                    if (!this.config.lazyLoad) {
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

                        // if no prevs found
                        if (!this.selectedFile) {
                            this.selectFirst()
                        }
                    }

                    // scroll to prev selected item
                    let curIndex = this.currentFileIndex
                    if (curIndex) {
                        let t = setInterval(() => {
                            if (document.readyState === 'complete') {
                                this.scrollToFile(this.$refs[`file_${curIndex}`])
                                clearInterval(t)
                            }
                        }, 500)
                    }

                    // scroll to breadcrumb item
                    if (this.$refs.bc) {
                        let name = folders.split('/').pop()
                        let count = document.getElementById(`${name ? name : 'library'}-bc`).offsetLeft
                        this.$refs.bc.$el.scrollBy({top: 0, left: count, behavior: 'smooth'})
                    }
                })

                return this.dirsListCheck(dirs)
            }

            // we dont have files
            this.toggleLoading()
            this.loadingFiles('hide')
        },
        dirsListCheck(data) {
            this.directories = data

            // check for hidden folders in directories
            if (this.hidePath.length) {
                this.directories = this.directories.filter((e) => {
                    return !this.checkForFolderName(e)
                })
            }

            if (this.lockedList.length) {
                // nested folders
                if (this.files.path !== '') {
                    return this.directories = this.directories.filter((e) => {
                        return !this.lockedList.includes(`${this.config.baseUrl}${this.folders.join('/')}${e}`)
                    })
                }

                // root
                this.directories = this.directories.filter((e) => {
                    return !this.lockedList.includes(`${this.config.baseUrl}${e}`)
                })
            }
        },

        /*                Tool-Bar                */
        NewFolderForm(event) {
            let folder_name = this.newFolderName
            let path = this.files.path

            if (!folder_name) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            if (folder_name.match(/^.\/.*|^.$/)) {
                return this.showNotif(this.trans('single_char_folder'), 'danger')
            }

            this.toggleLoading()

            axios.post(event.target.action, {
                current_path: path,
                new_folder_name: folder_name
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()
                this.resetInput('newFolderName')

                if (!data.success) {
                    return this.showNotif(data.message, 'danger')
                }

                this.showNotif(`${this.trans('create_success')} "${data.new_folder_name}" at "${path}"`)
                this.removeCachedResponse()
                this.getFiles(this.folders, data.new_folder_name)

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        // rename
        RenameFileForm(event) {
            let changed = this.newFilename

            if (!changed) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            if (this.selectedFileIs('folder') && changed.match(/^.\/.*|^.$/)) {
                return this.showNotif(this.trans('single_char_folder'), 'danger')
            }

            this.toggleLoading()

            let filename = this.selectedFile.name
            let ext = this.getExtension(filename)
            let newFilename = ext == null ? changed : `${changed}.${ext}`

            axios.post(event.target.action, {
                folder_location: this.files.path,
                filename: filename,
                new_filename: newFilename
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()

                if (!data.success) {
                    return this.showNotif(data.message, 'danger')
                }

                this.showNotif(`${this.trans('rename_success')} "${filename}" to "${data.new_filename}"`)
                this.updateItemName(this.selectedFile, filename, data.new_filename)
                this.removeCachedResponse()

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
                let destination = this.moveToPath
                let copy = this.useCopy
                let error = false
                let files = this.bulkItemsCount
                    ? this.bulkListFilter
                    : [this.selectedFile]

                this.toggleLoading()

                axios.post(event.target.action, {
                    folder_location: this.files.path,
                    destination: destination,
                    moved_files: files,
                    use_copy: copy
                }).then(({data}) => {
                    this.toggleLoading()
                    this.toggleModal()

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
                    this.removeCachedResponse(destination)

                    if (copy) {
                        this.removeCachedResponse('../')
                    }

                    this.isBulkSelecting()
                        ? this.blkSlct()
                        : error
                            ? false
                            : this.config.lazyLoad
                                ? false
                                : this.selectFirst()

                }).catch((err) => {
                    console.error(err)
                    this.ajaxError()
                })
            }
        },

        // delete
        DeleteFileForm(event) {
            let files = this.bulkItemsCount
                ? this.bulkListFilter
                : [this.selectedFile]

            this.toggleLoading()

            axios.post(event.target.action, {
                folder_location: this.files.path,
                deleted_files: files
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()

                data.res.map((item) => {
                    if (!item.success) {
                        return this.showNotif(item.message, 'danger')
                    }

                    if (!data.fullCacheClear) {
                        this.showNotif(`${this.trans('delete_success')} "${item.name}"`, 'warning')
                        this.removeFromLists(item.name, item.type)
                    }
                })

                this.$refs['success-audio'].play()
                this.isBulkSelecting()
                    ? this.blkSlct()
                    : this.config.lazyLoad
                        ? false
                        : this.selectFirst()

                this.$nextTick(() => {
                    if (data.fullCacheClear) {
                        this.clearCache(false)
                    } else {
                        this.removeCachedResponse('../')
                    }

                    if (this.searchFor) {
                        this.searchItemsCount = this.filesList.length
                    }
                })

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },

        // visibility
        SetVisibilityForm(event) {
            let list = this.bulkItemsCount
                ? this.bulkListFilter
                : [this.selectedFile]

            this.toggleLoading()

            axios.post(event.target.action, {
                type: this.visibilityType,
                list: list,
                path: this.files.path
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()

                data.res.map((item) => {
                    if (item.success) {
                        this.showNotif(item.message)
                    } else {
                        this.showNotif(item.message, 'danger')
                    }
                })

                list.map((e) => {
                    return e.visibility = this.visibilityType
                })

                this.$refs['success-audio'].play()
                this.removeCachedResponse()
                this.isBulkSelecting()
                    ? this.blkSlct()
                    : false

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
                this.removeCachedResponse()
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

            if (type == 'folder' && this.directories.length) {
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
