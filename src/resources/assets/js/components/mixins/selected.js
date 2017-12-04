export default {
    methods: {
        /*                Selected                */
        selectFirst() {
            this.$nextTick(() => {
                let file = this.$refs.file_0[0]

                if (file) {
                    file.click()
                }
            })
        },
        setSelected(file, index) {
            this.selectedFile = file
            this.currentFileIndex = index

            if (this.isBulkSelecting()) {
                this.pushtoBulkList(file)
            }
        },
        selectedFileIs(val) {
            if (this.selectedFile !== null) {
                return this.fileTypeIs(this.selectedFile, val)
            }
        },

        /*                Folder                */
        openFolder(file) {
            if (!this.isBulkSelecting()) {
                if (!this.fileTypeIs(file, 'folder')) {
                    return
                }

                this.folders.push(file.name)
                this.getFiles(this.folders)
            }

            this.resetInput('currentFilterName')
        },
        goToFolder(index) {
            if (!this.isBulkSelecting()) {
                this.noFiles('hide')
                this.resetInput('currentFilterName')

                if (this.checkForRestrictedPath() && index == 0) {
                    return
                }

                let prev_folder_name = this.folders[index]

                this.folders = this.folders.splice(0, index)
                this.getFiles(this.folders, prev_folder_name)
            }
        },
        goToPrevFolder() {
            let length = this.folders.length
            let newSelected = length - 1

            if (length == 0 || this.restrictPath && this.files.path == `/${this.restrictPath}`) {
                return
            }

            this.goToFolder(newSelected)
        },

        /*                Navigation                */
        navigation(e) {
            // go to prev item
            if (keycode(e) == 'left' || keycode(e) == 'up') {
                e.preventDefault()
                this.navDirection = 'prev'
                this.goToPrev()
            }

            // go to next item
            if (keycode(e) == 'right' || keycode(e) == 'down') {
                e.preventDefault()
                this.navDirection = 'next'
                this.goToNext()
            }

            // go to last item
            if (keycode(e) == 'end') {
                e.preventDefault()
                this.navDirection = 'next'

                let last = this.filesList.length - 1
                this.scrollToFile(this.$refs[`file_${last}`])
            }

            // go to first item
            if (keycode(e) == 'home') {
                e.preventDefault()
                this.navDirection = 'prev'
                this.scrollToFile(this.$refs.file_0)
            }

            // toggle modal off
            if (this.isActiveModal('preview_modal') && !this.selectedFileIs('image')) {
                this.toggleModal()
            }
        },
        goToPrev() {
            let curSelectedIndex = this.currentFileIndex

            if (curSelectedIndex !== 0) {
                let newSelected = curSelectedIndex - 1
                this.scrollToFile(this.$refs[`file_${newSelected}`])
            }
        },
        goToNext() {
            let curSelectedIndex = this.currentFileIndex

            if (curSelectedIndex < this.allItemsCount - 1) {
                let newSelected = curSelectedIndex + 1
                this.scrollToFile(this.$refs[`file_${newSelected}`])
            }
        },
        scrollToFile(file) {
            file = file[0]
            let container = this.$refs.left.$el
            let offset = container.style.paddingTop + file.style.marginTop

            file.click()
            file.scrollIntoView(false)

            // respect container & file offset when scrolling
            if (file.offsetTop > container.offsetHeight) {
                container.scrollTop += offset
            }
        }
    }
}
