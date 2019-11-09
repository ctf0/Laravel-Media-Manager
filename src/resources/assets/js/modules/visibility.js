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

            list = list.filter((e) => e.type != 'folder')

            axios.post(this.routes.visibility, {
                path: this.files.path,
                list: list
            }).then(({data}) => {

                let files = this.files.items

                data.map((item) => {
                    if (item.success) {
                        files.some((e) => {
                            if (e.name == item.name) {
                                return e.visibility = item.visibility
                            }
                        })

                        this.showNotif(item.message)
                    } else {
                        this.showNotif(item.message, 'danger')
                    }
                })

                this.isBulkSelecting() ? this.blkSlct() : false

            }).catch((err) => {
                console.error(err)
                this.ajaxError()
            })
        }
    }
}
