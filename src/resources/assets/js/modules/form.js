import Dropzone from 'dropzone'

export default {
    methods: {
        /*                Upload                */
        fileUpload() {
            let manager = this
            let uploadTypes = this.restrict.uploadTypes ? this.restrict.uploadTypes.join(',') : null
            let uploadsize = this.restrict.uploadsize ? this.restrict.uploadsize : 256

            let last = null
            let sending = false
            let clearCache = false
            let options = {
                url: manager.routes.upload,
                createImageThumbnails: false,
                parallelUploads: 10,
                hiddenInputContainer: '#new-upload',
                uploadMultiple: true,
                forceFallback: false,
                acceptedFiles: uploadTypes,
                maxFilesize: uploadsize,
                headers: {
                    'X-Socket-Id': manager.browserSupport('Echo') ? Echo.socketId() : null,
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                timeout: 3600000, // 60 mins
                previewsContainer: '#uploadPreview',
                processingmultiple() {
                    manager.showProgress = true
                },
                sending(file, xhr, formData) {
                    sending = true
                    formData.append('upload_path', manager.files.path)
                    formData.append('random_names', manager.randomNames)
                },
                totaluploadprogress(uploadProgress) {
                    manager.progressCounter = `${Math.round(uploadProgress)}%`
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
                    // this.removeFile(file)
                },
                queuecomplete() {
                    if (!sending) {
                        manager.hideProgress()

                        if (clearCache) {
                            manager.removeCachedResponse().then(() => {
                                last
                                    ? manager.getFiles(manager.folders, null, last)
                                    : manager.getFiles(manager.folders)
                            })
                        }
                    }
                }
            }

            // upload panel
            new Dropzone('#new-upload', options)
            // drag  drop on empty area
            new Dropzone('.__stack-container', Object.assign(options, {
                clickable: false
            }))
        },

        // upload image from link
        saveLinkForm(event) {
            let url = this.urlToUpload

            if (!url) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            this.UploadArea = false
            this.toggleLoading()
            this.loadingFiles('show')

            this.$nextTick(() => {
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
            })
        },

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

            // clear expired cache
            return this.invalidateCache().then(() => {
                // fix db-click to open folder not reseting the file selection
                this.resetInput(['selectedFile', 'currentFileIndex'])

                // get data
                return this.getCachedResponse().then((res) => {
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

                        this.isLoading = false
                        this.loadingFiles('hide')
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
            let lazy = this.lazyModeIsOn()
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
                            index = lazy
                                ? this.fileTypeIs(e, 'image')
                                    ? null
                                    : i
                                : i
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
                        lazy ? this.lazySelectFirst() : this.selectFirst()
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
                                        : this.lazyModeIsOn()
                                            ? this.lazySelectFirst()
                                            : this.selectFirst()
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
                    // clear cache storage for images / audio
                    else {
                        this.deleteCachedResponse(item.url)
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
                                ? this.lazyModeIsOn()
                                    ? this.lazySelectFirst()
                                    : this.selectFirst()
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
