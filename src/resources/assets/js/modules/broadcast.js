export default {
    mounted() {
        if (this.browserSupport('Echo') && this.config.broadcasting) {
            /**
             * public
             */
            // async ops
            Echo.channel('User.media')
                .listen('.user.media.ops', ({data}) => {
                    switch (data.op) {
                        case 'upload':
                            this.bcUpload(data)
                            break
                        case 'new_folder':
                            this.bcNewFolder(data)
                            break
                        case 'move':
                            this.bcMove(data)
                            break
                        case 'rename':
                            this.bcRename(data)
                            break
                        case 'delete':
                            this.bcDelete(data)
                            break
                        case 'lock':
                            this.bcLock()
                            break
                        case 'visibility':
                            this.bcVisibility(data)
                            break
                    }
                })

            /**
             * private
             */
            // download
            Echo.private(`User.${this.userId}.media`)
                .listen('.user.media.zip', ({data}) => {
                    this.bcZip(data)
                })
        }
    },
    methods: {
        bcNotif(msg) {
            return this.showNotif(msg, 'link')
        },

        // ops
        bcUpload(data) {
            let path = data.path || ''

            // if user is viewing the same dir
            if (path == this.files.path) {
                this.bcNotif(`${this.trans('new_uploads_notif')}, ${this.trans('refresh_notif')}`)
            }
        },
        bcNewFolder(data) {
            let path = data.path || ''

            // if user is viewing the same dir
            if (path == this.files.path) {
                this.bcNotif(`${this.trans('create_folder_notif')}, ${this.trans('refresh_notif')}`)
            }
        },
        bcMove(data) {
            let path = data.path || ''

            // if user is viewing the same dir
            if (path == this.files.path) {
                this.db('clr')
                this.bcNotif(`${this.trans('move_success')}, ${this.trans('refresh_notif')}`)
            }
        },
        bcRename(data) {
            let path = data.path || ''

            // if user is viewing the same dir
            if (path == this.files.path) {
                let item = data.item

                if (this.filteredItemsCount) {
                    this.filterdFilesList.some((e) => {
                        if (e.name == item.oldName && e.type == item.type) {
                            e.name = item.newName

                            return e.path.replace(item.oldName, item.newName)
                        }
                    })
                }

                this.files.items.some((e) => {
                    if (e.name == item.oldName && e.type == item.type) {
                        e.name = item.newName

                        return e.path.replace(item.oldName, item.newName)
                    }
                })

                this.bcNotif(`${this.trans('rename_success')} "${item.oldName}" to "${item.newName}"`)
            }
        },
        bcDelete(data) {
            let path = data.path || ''

            // if user is viewing the same dir
            if (path == this.files.path) {
                this.db('clr')

                data.items.map((item) => {
                    let storage_path = item.path

                    this.removeFromLists(storage_path, storage_path == this.selectedFile.storage_path)
                    this.bcNotif(`${this.trans('delete_success')} "${item.name}"`)
                })
            }
        },
        bcLock() {
            this.updateLockList()
        },
        bcVisibility(data) {
            // if user is viewing the same dir
            if (data.path || this.files.path == '') {
                let files = this.files.items
                let filterd = this.filteredItemsCount ? this.filterdFilesList : null

                data.items.map((item) => {
                    files.some((e) => {
                        if (e.name == item.name) {
                            return e.visibility = item.visibility
                        }
                    })

                    if (filterd) {
                        filterd.some((e) => {
                            if (e.name == item.name) {
                                return e.visibility = item.visibility
                            }
                        })
                    }
                })
            }
        },
        bcZip(data) {
            if (data.progress) {
                this.progressCounter = `${data.progress}%`

                if (data.progress >= 100) {
                    setTimeout(this.hideProgress, 500)
                }
            }

            if (data.type == 'warn') {
                this.showNotif(data.msg, 'warning')
            }
        }
    }
}
