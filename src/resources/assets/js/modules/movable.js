import uniq from 'lodash/uniq'

export default {
    computed: {
        movableItemsCount() {
            return this.movableList.length
        },
        movableItemsFilter() {
            return this.lockedList.length
                ? this.movableList.filter((e) => !this.IsLocked(e.path))
                : this.movableList
        },
        movableItemsFilterSize() {
            return this.getListTotalSize(this.movableItemsFilter)
        }
    },
    methods: {
        showMovableList() {
            this.$refs.move.click()
        },
        // helpers
        addToMovableList(item = null) {
            let list = [].concat(
                this.movableList,
                item
                    ? [item]
                    : this.delOrMoveList()
            )

            this.movableList = uniq(list)

            if (this.isBulkSelecting()) {
                return this.$refs.bulkSelect.click()
            }
        },
        removeFromMovableList(i) {
            return this.movableList.splice(i, 1)
        },
        clearMovableList(notif = true) {
            this.resetInput('movableList', [])

            return notif ? this.showNotif(this.trans('move_clear')) : false
        },
        inMovableList(item = this.selectedFile) {
            return item &&
                this.movableItemsCount &&
                this.movableList.some((e) => e.storage_path == item.storage_path)
        },

        // form
        MoveFileForm(event) {
            let hasErrors = false
            let destination = this.files.path
            let copy = this.copyFilesNotMove
            let files = this.checkForNestedLockedItems(this.movableItemsFilter)

            if (!files.length) {
                return this.toggleModal()
            }

            this.toggleLoading()

            axios.post(event.target.action, {
                destination: destination,
                moved_files: files,
                use_copy: copy
            }).then(({data}) => {
                this.toggleLoading()
                this.toggleModal()

                let paths = []

                data.map((item) => {
                    if (!item.success) {
                        hasErrors = true

                        return this.showNotif(item.message, 'danger')
                    }

                    let msg = this.restrictModeIsOn
                        ? `"${item.name}"`
                        : `"${item.name}" to "${destination || '/'}"`

                    paths.push(item.old_path)
                    this.showNotif(`${this.trans(copy ? 'copy_success' : 'move_success')} ${msg}`)
                })

                if (!hasErrors) {
                    // clear all
                    this.clearMovableList(false)
                } else {
                    // remove succeeded items
                    paths.map((p) => {
                        this.movableList.some((e, i) => {
                            if (e.storage_path == p) {
                                this.movableList.splice(i, 1)
                            }
                        })
                    })
                }

                this.db('clr')
                this.refresh()

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        }
    }
}
