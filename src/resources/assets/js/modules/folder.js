export default {
    methods: {
        // navigation
        openFolder(file) {
            if (this.fileTypeIs(file, 'folder')) {
                this.folders.push(file.name)
                this.getFiles(this.folders).then(() => {
                    this.updatePageUrl()
                })
            }
        },
        goToFolder(index) {
            if (!this.isBulkSelecting()) {
                let folders = this.folders
                let prev_folder_name = folders[index]

                this.folders = folders.splice(0, index)

                this.getFiles(this.folders, prev_folder_name).then(() => {
                    this.updatePageUrl()
                })
            }
        },
        goToPrevFolder(e = null, cls = null) {
            EventHub.fire('stopHammerPropagate')
            let manager = this

            function run() {
                if (manager.restrictModeIsOn()) {
                    return false
                }

                let length = manager.folders.length

                return length == 0
                    ? false
                    : manager.goToFolder(length - 1)
            }

            e && e.target.classList.contains(cls) ? run() : run()
        },

        // misc
        updateMoveList() {
            this.moveToPath = this.$refs.move_folder_dropdown.options[0].value
        },

        // filter
        filterDirList() {
            return this.directories.filter((dir) => {
                // dont show dirs that have similarity with selected item(s)
                if (this.bulkItemsCount) {
                    return this.bulkList.filter((e) => dir.match(`(/?)${e.name}(/?)`)).length > 0 ? false : true
                }

                return this.selectedFile && !dir.includes(this.selectedFile.name)
            })
        },

        // check
        moveUpCheck() {
            this.$nextTick(() => {
                this.checkForFolders = this.$refs.move_folder_dropdown.options[0] ? true : false
            })

            return this.allItemsCount && this.folders.length
        },
        dirsListCheck() {
            const baseUrl = this.config.baseUrl

            // check for hidden folders in directories
            if (this.hidePath.length) {
                this.directories = this.directories.filter((e) => !this.checkForFolderName(e))
            }

            if (this.lockedList.length) {
                // nested folders
                if (this.files.path !== '') {
                    return this.directories = this.directories.filter(
                        (e) => !this.IsLocked(
                            this.clearDblSlash(`${baseUrl}/${this.folders.join('/')}/${e}`)
                        )
                    )
                }

                // root
                this.directories = this.directories.filter(
                    (e) => !this.IsLocked(this.clearDblSlash(`${baseUrl}/${e}`))
                )
            }
        }
    }
}
