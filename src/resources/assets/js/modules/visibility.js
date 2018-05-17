export default {
    methods: {
        IsVisible(file) {
            if (file && file.visibility) {
                return file.visibility == 'public'
            }

            return true
        },

        // form
        FileVisibilityForm() {
            let list = this.bulkItemsCount
                ? this.bulkList
                : [this.selectedFile]

            list = list.filter((e) => {
                return e.type != 'folder'
            })

            axios.post(this.routes.visibility, {
                path: this.files.path,
                list: list
            }).then(({data}) => {

                data.map((item) => {
                    if (item.success) {
                        this.showNotif(item.message)
                        this.files.items.some((e) => {
                            if (e.name == item.name) {
                                return e.visibility = item.visibility
                            }
                        })
                    } else {
                        this.showNotif(item.message, 'danger')
                    }
                })

                this.$refs['success-audio'].play()
                this.isBulkSelecting() ? this.blkSlct() : false
                this.removeCachedResponse()

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        }
    }
}