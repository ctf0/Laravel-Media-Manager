import debounce from 'lodash/debounce'
const Dropzone = require('dropzone')

export default {
    methods: {
        /*                Upload                */
        fileUpload() {
            const manager = this
            let uploadTypes = this.restrict.uploadTypes ? this.restrict.uploadTypes.join(',') : null
            let uploadsize = this.restrict.uploadsize ? this.restrict.uploadsize : 256

            let last = null
            let sending = false
            let clearCache = false

            new Dropzone('#new-upload', {
                createImageThumbnails: false,
                parallelUploads: 10,
                hiddenInputContainer: '#new-upload',
                uploadMultiple: true,
                forceFallback: false,
                ignoreHiddenFiles: true,
                acceptedFiles: uploadTypes,
                maxFilesize: uploadsize,
                headers: {
                    'X-Socket-Id': manager.browserSupport('Echo') ? Echo.socketId() : null
                },
                timeout: 3600000, // 60 mins
                previewsContainer: '#uploadPreview',
                processingmultiple() {
                    manager.showProgress = true
                },
                sending() {
                    sending = true
                },
                totaluploadprogress(uploadProgress) {
                    manager.progressCounter = `${uploadProgress}%`
                },
                successmultiple(files, res) {
                    res.map((item) => {
                        if (item.success) {
                            clearCache = true
                            last = item.file_name
                            manager.showNotif(`${manager.trans('upload_success')} "${item.file_name}"`)
                        } else {
                            manager.showNotif(item.message, 'danger')
                        }
                    })

                    sending = false
                },
                errormultiple(file, res) {
                    file = Array.isArray(file) ? file[0] : file
                    manager.showNotif(`"${file.name}" ${res}`, 'danger')
                },
                queuecomplete() {
                    if (!sending) {
                        manager.progressCounter = 0
                        manager.showProgress = false

                        if (clearCache) {
                            manager.removeCachedResponse().then(() => {
                                last
                                    ? manager.getFiles(manager.folders, null, last)
                                    : manager.getFiles(manager.folders)
                            })
                        } else {
                            manager.toggleInfo = true
                            manager.isLoading = false
                            manager.loadingFiles('hide')
                        }
                    }
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
                this.removeCachedResponse().then(() => {
                    this.getFiles(this.folders, null, data.message)
                })

            }).catch((err) => {
                console.error(err)
                this.toggleLoading()
                this.toggleModal()
                this.loadingFiles('hide')

                this.ajaxError()
            })
        },

        /*                Main                */
        getFiles(folders = null, prev_folder = null, prev_file = null) {
            this.resetInput(['sortBy', 'currentFilterName', 'selectedFile', 'currentFileIndex'])
            this.noFiles('hide')

            if (!this.loading_files) {
                this.isLoading = true
                this.toggleInfo = false
                this.loadingFiles('show')
            }

            if (folders) {
                folders = this.clearDblSlash(`/${folders.join('/')}`)
            }

            // clear expired cache
            return this.invalidateCache().then(() => {
                // get data
                return this.getCachedResponse()
                    .then((res) => {
                        // return cache
                        if (res) {
                            this.files = res.files
                            this.directories = res.dirs
                            return this.filesListCheck(folders, prev_folder, prev_file)
                        }

                        // or make new call
                        return axios.post(this.routes.files, {
                            folder: folders,
                            dirs: this.folders
                        }).then(({data}) => {
                            // folder doesnt exist
                            if (data.error) {
                                return this.showNotif(data.error, 'danger')
                            }

                            // cache response
                            this.cacheResponse({
                                files: data.files,
                                dirs: data.dirs
                            })

                            // return data
                            this.files = data.files
                            this.lockedList = data.locked
                            this.directories = data.dirs
                            this.filesListCheck(folders, prev_folder, prev_file)

                        }).catch((err) => {
                            console.error(err)
                            this.ajaxError()
                        })
                    })
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
            // check for hidden extensions
            if (this.hideExt.length) {
                this.files.items = this.files.items.filter((e) => !this.checkForHiddenExt(e))
            }

            // check for hidden folders
            if (this.hidePath.length) {
                this.files.items = this.files.items.filter((e) => !this.checkForHiddenPath(e))
            }

            // hide folders for restrictionMode
            if (this.restrictModeIsOn()) {
                this.files.items = this.files.items.filter((e) => e.type != 'folder')
            }

            // we have files
            if (this.allItemsCount) {
                // check for prev opened folder
                if (prev_folder) {
                    this.files.items.some((e, i) => {
                        if (e.name == prev_folder) {
                            return this.currentFileIndex = i
                        }
                    })
                }

                // lazy loading is not active
                if (!this.lazyModeIsOn()) {
                    // check for prev selected file
                    if (prev_file) {
                        this.files.items.some((e, i) => {
                            if (e.name == prev_file) {
                                return this.currentFileIndex = i
                            }
                        })
                    }

                    // if no prevs found
                    if (!this.currentFileIndex) {
                        this.selectFirst()
                    }
                } else {
                    // lazy loading is active & first file is a folder
                    if (this.fileTypeIs(this.allFiles[0], 'folder')) {
                        this.selectFirst()
                    }
                }

                if (this.searchFor) {
                    this.updateSearchCount()
                }

                this.dirsListCheck()
            }

            this.toggleInfo = true
            this.isLoading = false
            this.loadingFiles('hide')

            // avoid unnecessary delay
            if (this.firstRun && this.allItemsCount > 20) {
                this.$nextTick(debounce(() => {
                    this.scrollOnLoad(folders)
                }, 500))
            } else {
                this.$nextTick(() => {
                    this.scrollOnLoad(folders)
                })
            }
        },
        dirsListCheck() {
            const baseUrl = this.config.baseUrl

            // check for hidden folders in directories
            if (this.hidePath.length) {
                this.directories = this.directories.filter((e) => !this.checkForFolderName(e))
            }

            if (this.lockedList.length) {
                // nested folders
                if (this.files.path !== '') {
                    return this.directories = this.directories.filter(
                        (e) => !this.IsLocked(
                            this.clearDblSlash(`${baseUrl}/${this.folders.join('/')}/${e}`)
                        )
                    )
                }

                // root
                this.directories = this.directories.filter(
                    (e) => !this.IsLocked(this.clearDblSlash(`${baseUrl}/${e}`))
                )
            }
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
                this.deleteCachedResponse(this.cacheName).then(() => {
                    this.getFiles(this.folders, data.new_folder_name)
                })

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
            let cacheName = this.getCacheName(filename)
            let ext = this.getExtension(filename)
            let newFilename = ext == null ? changed : `${changed}.${ext}`

            if (!changed) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            if (this.selectedFileIs('folder') && this.hasLockedItems(filename, cacheName)) {
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

                // clear image cache
                if (this.selectedFileIs('image')) {
                    this.removeImageCache(this.selectedFile.path)
                }

                let savedName = data.new_filename

                this.showNotif(`${this.trans('rename_success')} "${filename}" to "${savedName}"`)
                selected.name = savedName
                selected.path = selected.path.replace(filename, savedName)

                // clear folders cache
                if (this.selectedFileIs('folder')) {
                    this.updateDirsList()
                    this.removeCachedResponse(null, [cacheName])
                } else {
                    this.removeCachedResponse()
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
                let clearCache = false
                let cacheNamesList = []

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

                        clearCache = true

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

                            if (this.fileTypeIs(item, 'folder')) {
                                cacheNamesList.push(`${this.cacheName}/${item.name}`)
                            }
                        }

                        // update folder count when folder is moved/copied into another
                        this.fileTypeIs(item, 'folder')
                            ? this.updateFolderCount(destination, item.items, item.size)
                            : this.updateFolderCount(destination, 1, item.size)
                    })

                    if (clearCache) {
                        this.clearImageCache()
                        this.removeCachedResponse(destination == '../' ? null : destination, cacheNamesList).then(() => {
                            if (this.allItemsCount) {
                                this.isBulkSelecting()
                                    ? this.blkSlct()
                                    : hasErrors
                                        ? false
                                        : !this.lazyModeIsOn()
                                            ? this.selectFirst()
                                            : this.lazySelectFirst()
                            }
                        })
                    }

                }).catch((err) => {
                    console.error(err)
                    this.ajaxError()
                })
            }
        },

        // delete
        DeleteFileForm(event) {
            let clearCache = false
            let cacheNamesList = []
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

                    // clear indexdb cache for dirs
                    if (item.type == 'folder') {
                        cacheNamesList.push(this.getCacheName(item.name))
                    }
                    // clear cache storage for images
                    else {
                        this.removeImageCache(item.url)
                    }

                    clearCache = true
                    this.showNotif(`${this.trans('delete_success')} "${item.name}"`)
                    this.removeFromLists(item.name, item.type)
                })

                if (clearCache) {
                    this.removeCachedResponse(null, cacheNamesList).then(() => {
                        this.isBulkSelecting()
                            ? this.blkSlct()
                            : this.allItemsCount
                                ? !this.lazyModeIsOn()
                                    ? this.selectFirst()
                                    : this.lazySelectFirst()
                                : false
                    })
                }

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
