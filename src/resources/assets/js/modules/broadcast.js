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
                            this.bcLock(data)
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
            return this.showNotif(msg, 'info')
        },

        // ops
        bcUpload(data) {
            let path = data.path || 'root_'

            // if user is viewing the same dir
            if (path == this.cacheName) {
                this.bcNotif(`${this.trans('new_uploads_notif')}, ${this.trans('refresh_notif')}`)
            }
        },
        bcNewFolder(data) {
            let path = data.path || 'root_'

            // if user is viewing the same dir
            if (path == this.cacheName) {
                this.bcNotif(`${this.trans('create_folder_notif')}, ${this.trans('refresh_notif')}`)
            }
        },
        bcMove(data) {
            let selected = this.selectedFile
            let path = data.path
            let reselect = false
            let check = path.current || 'root_'

            // if user is viewing the same dir
            if (check == this.cacheName || path.new == this.cacheName) {
                data.items.map((item) => {
                    this.removeFromLists(item.name, item.type, false)

                    if (selected && (selected.name == item.name && selected.type == item.type)) {
                        reselect = true
                    }

                    // this.bcNotif(`${this.trans('new_uploads_notif')}, ${this.trans('refresh_notif')}`)
                    // this.bcNotif(`${this.trans('move_success')} "${item.name}", ${this.trans('refresh_notif')}`)
                })
            }

            if (reselect) this.selectFirst()
        },
        bcRename(data) {
            let path = data.path || 'root_'

            // if user is viewing the same dir
            if (path == this.cacheName) {
                let item = data.item

                if (this.filteredItemsCount) {
                    this.filterdList.some((e) => {
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
            let selected = this.selectedFile
            let reselect = false
            let current = data.path || this.cacheName == 'root_'

            data.items.map((item) => {
                // if user is viewing the same dir
                if (current) {
                    this.removeFromLists(item.name, item.type, false)
                    this.bcNotif(`${this.trans('delete_success')} "${item.name}"`)

                    if (selected.name == item.name && selected.type == item.type) {
                        reselect = true
                    }
                }
            })

            if (reselect) this.selectFirst()
        },
        bcLock(data) {
            // if user is viewing the same dir
            if (data.path || this.cacheName == 'root_') {
                this.updateLockOps(data)
            }
        },
        bcVisibility(data) {
            // if user is viewing the same dir
            if (data.path || this.cacheName == 'root_') {
                let files = this.files.items
                let filterd = this.filteredItemsCount ? this.filterdList : null

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
