export default {
    methods: {
        IsLocked(item) {
            if (item) {
                return item.path
                    ? this.hasLockedItems(item.path)
                    : this.hasLockedItems(item)
            }
        },
        hasLockedItems(url, side = 'end') {
            return side == 'end'
                ? this.lockedList.some((e) => e.endsWith(url))
                : this.lockedList.some((e) => e.startsWith(url))
        },
        checkForNestedLockedItems(list) {
            return list.filter((e, i) => {
                if (e.type == 'folder' && this.hasLockedItems(e.path, 'start')) {
                    this.showNotif(`"${e.name}" ${this.trans('error_altered_fwli')}`, 'danger')

                    return false
                }

                return true
            })
        },

        // form
        lockFileForm() {
            let list = this.bulkItemsCount
                ? this.bulkList
                : [this.selectedFile]

            axios.post(this.routes.lock, {
                path: this.files.path,
                list: list
            }).then(({data}) => {

                data.result.map((item) => {
                    this.showNotif(item.message)
                })

                this.updateLockList()
                this.resetInput(['currentFilterName'])
                this.isBulkSelecting() ? this.blkSlct() : false

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        },
        updateLockList(data) {
            return axios.post(this.routes.locked_list, {
                path: this.files.path
            }).then(({data}) => {
                this.lockedList = data.locked
            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        }
    }
}
