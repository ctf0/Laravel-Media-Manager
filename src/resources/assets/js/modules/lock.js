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

        // form
        lockFileForm() {
            let list = this.bulkItemsCount
                ? this.bulkList
                : [this.selectedFile]

            axios.post(this.routes.lock, {
                list: list
            }).then(({data}) => {

                data.map((item) => {
                    this.showNotif(item.message)
                })

                this.$refs['success-audio'].play()
                this.isBulkSelecting() ? this.blkSlct() : false
                this.removeCachedResponse().then(() => {
                    this.getFiles(this.folders)
                })

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        }
    }
}