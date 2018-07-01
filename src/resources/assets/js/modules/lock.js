export default {
    methods: {
        IsLocked(item) {
            if (item) {
                if (item.path) {
                    return this.lockedList.includes(item.path)
                }

                return this.lockedList.includes(item)
            }
        },

        // for folders with nested items
        hasLockedItems(name, cacheName) {
            return this.lockedList.some((e) => {
                return e.startsWith(this.clearDblSlash(`${this.config.baseUrl}/${cacheName}`))
            })
        },
        checkNestedLockedItems(list) {
            list.map((e) => {
                if (e.type == 'folder') {
                    let name = e.name
                    let cacheName = this.getCacheName(name)

                    if (this.hasLockedItems(name, cacheName)) {
                        this.showNotif(`"${cacheName}" ${this.trans('error_altered_fwli')}`, 'danger')
                        list.splice(list.indexOf(e), 1)
                    }
                }
            })

            return list
        },
        updateLockOps(data) {
            let path = this.files.path
            let dirs = this.directories
            let locked = this.lockedList

            data.removed.map((item) => {
                // dirslist
                if (item.type == 'folder') {
                    let name = path
                        ? `${path}/${item.name}`
                        : this.cacheName == 'root_'
                            ? item.name
                            : `/${item.name}`

                    dirs.push(name)
                }

                // locklist
                locked.splice(locked.indexOf(item.url), 1)
            })

            data.added.map((item) => {
                // dirslist
                if (item.type == 'folder') {
                    let name = path ? `${path}/${item.name}` : item.name
                    dirs.splice(dirs.indexOf(name), 1)
                }

                // locklist
                locked.push(item.url)
            })
        },

        // form
        lockFileForm() {
            let list = this.bulkItemsCount
                ? this.bulkList
                : [this.selectedFile]

            axios.post(this.routes.lock, {
                list: list,
                path: this.files.path
            }).then(({data}) => {

                data.result.map((item) => {
                    this.showNotif(item.message)
                })

                this.updateLockOps(data)
                this.resetInput(['currentFilterName'])
                this.isBulkSelecting() ? this.blkSlct() : false
                this.removeCachedResponse()

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        }
    }
}
