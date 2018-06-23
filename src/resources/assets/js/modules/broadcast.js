import uniq from 'lodash/uniq'

export default {
    mounted() {
        if (this.browserSupport('Echo') && this.config.broadcasting) {
            // async ops
            Echo.channel('User.media')
                .listen('.user.media.ops', ({data}) => {

                    // upload
                    if (data.op == 'upload') {
                        let path = data.path || 'root_'

                        this.removeCachedResponseForOther([path]).then(() => {
                            // if user is viewing the same dir
                            if (path == this.cacheName) {
                                this.showNotif(this.trans('new_uploads_notif'), 'info')
                            }
                        })
                    }

                    // new_folder
                    if (data.op == 'new_folder') {
                        this.deleteCachedResponse(data.path || 'root_')
                    }

                    // move
                    if (data.op == 'move') {
                        let selected = this.selectedFile
                        let path = data.path
                        let cacheNamesList = [path.old || 'root_', path.new || 'root_']
                        let reselect = false

                        // if user is viewing the same dir
                        if (path.current || 'root_' == this.cacheName) {
                            data.items.map((item) => {
                                this.removeFromLists(item.name, item.type, false)

                                if (selected.name == item.name && selected.type == item.type) {
                                    reselect = true
                                }
                            })
                        }

                        if (reselect) this.selectFirst()
                        this.removeCachedResponseForOther(uniq(cacheNamesList))
                    }

                    // rename
                    if (data.op == 'rename') {
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
                        }

                        this.removeCachedResponseForOther([path])
                    }

                    // delete
                    if (data.op == 'delete') {
                        let selected = this.selectedFile
                        let cacheNamesList = []
                        let reselect = false
                        let current = data.path || 'root_' == this.cacheName

                        data.items.map((item) => {
                            // if user is viewing the same dir
                            if (current) {
                                this.removeFromLists(item.name, item.type, false)
                                this.removeImageCache(item.url)
                                this.showNotif(`${this.trans('delete_success')} "${item.name}"`)

                                if (selected.name == item.name && selected.type == item.type) {
                                    reselect = true
                                }
                            }

                            // clear indexdb cache for dirs
                            cacheNamesList.push(item.path ? this.clearDblSlash(`/${item.path}`) : 'root_')
                        })

                        if (reselect) this.selectFirst()
                        this.removeCachedResponseForOther(uniq(cacheNamesList))
                    }

                    // lock
                    if (data.op == 'lock') {
                        // if user is viewing the same dir
                        if (data.path || 'root_' == this.cacheName) {
                            this.updateLockOps(data)
                        }

                        this.removeCachedResponseForOther([data.path])
                    }

                    // visibility
                    if (data.op == 'visibility') {
                        // if user is viewing the same dir
                        if (data.path || 'root_' == this.cacheName) {
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

                        this.removeCachedResponseForOther([data.path])
                    }
                })

            // download
            Echo.private(`User.${this.userId}.media`)
                .listen('.user.media.zip', ({data}) => {
                    if (data.progress) {
                        this.progressCounter = `${data.progress}%`

                        if (data.progress >= 100) {
                            setTimeout(() => {
                                this.hideProgress()
                            }, 500)
                        }
                    }

                    if (data.type == 'warn') {
                        this.showNotif(data.msg, 'warning')
                    }
                })
        }
    }
}
