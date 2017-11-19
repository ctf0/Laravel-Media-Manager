export default {
    methods: {
        /*                Main                */
        getFiles(folders, select_prev = null) {

            this.toggleLoading()
            this.clearSelected()
            this.toggleInfoPanel()
            this.noFiles('hide')
            this.loadingFiles('show')
            this.resetInput(['searchFor', 'sortBy', 'currentFilterName'])

            let folder_location = '/'

            if (folders !== '/') {
                folder_location = '/' + folders.join('/')
            }

            // files list
            $.post(this.filesRoute, {
                folder: folder_location
            }, (res) => {
                if (res.error) {
                    if (this.checkForRestrictedPath()) {
                        EventHub.fire('get-folders', false)
                    }

                    this.noFiles('show')
                    return this.showNotif(res.error, 'danger')
                }

                // check for restricted path
                if (this.checkForRestrictedPath()) {
                    EventHub.fire('get-folders', true)
                }

                this.files = res

                // check for prev opened folder
                if (select_prev) {
                    this.$nextTick(() => {
                        this.files.items.map((e) => {
                            if (e.name == select_prev) {
                                return this.setSelected(e)
                            }
                        })
                    })
                } else if (this.allItemsCount) {
                    this.selectFirst()
                }

                // check for restricted extensions
                if (this.restrictExt.length) {
                    this.files.items = this.files.items.filter((e) => {
                        return !this.checkForRestrictedExt(e)
                    })
                }

                this.toggleLoading()
                this.loadingFiles('hide')
                this.toggleInfoPanel()
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
                return this.showNotif(this.trans('no_val'), 'warning')
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
                return this.showNotif(this.trans('no_val'), 'warning')
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
                    this.move_file(this.bulkListFilter, event.target.action)

                    setTimeout(() => {
                        this.blkSlct()
                    }, 100)
                } else {
                    this.move_file([this.selectedFile], event.target.action)
                }
            }
        },
        DeleteFileForm(event) {
            if (this.bulkItemsCount) {
                this.delete_file(this.bulkListFilter, event.target.action)

                setTimeout(() => {
                    this.blkSlct()
                }, 100)
            } else {
                this.delete_file([this.selectedFile], event.target.action)
            }
        }
    }
}
