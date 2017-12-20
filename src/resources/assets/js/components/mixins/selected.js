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
        setSelected(file, index, e = null) {
            // select with shift
            if (e && e.shiftKey) {
                this.bulkSelect = true

                // normal
                let begin = this.currentFileIndex
                let end = index + 1

                // reverse
                if (begin > index) {
                    begin = index
                    end = this.currentFileIndex + 1
                }

                return this.bulkList = this.allFiles.slice(begin, end)
            }

            // normal selection
            this.selectedFile = file
            this.currentFileIndex = index

            // bulk selection
            if (this.isBulkSelecting()) {
                this.pushtoBulkList(file)
            }
        },
        selectedFileIs(val) {
            if (this.selectedFile !== null) {
                return this.fileTypeIs(this.selectedFile, val)
            }
        },
        dbltap() {
            return this.selectedFileIs('image') || this.selectedFileIs('pdf') || this.selectedFileIs('text')
                ? this.toggleModal('preview_modal')
                : this.openFolder(this.selectedFile)
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
                this.goToPrev()
            }

            // go to next item
            if (keycode(e) == 'right' || keycode(e) == 'down') {
                e.preventDefault()
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
            if (
                this.isActiveModal('preview_modal') &&
                !(
                    this.selectedFileIs('image') ||
                    this.selectedFileIs('pdf') ||
                    this.selectedFileIs('text')
                )
            ) {
                this.toggleModal()
            }
        },
        goToPrev() {
            let curSelectedIndex = this.currentFileIndex

            if (curSelectedIndex !== 0) {
                this.navDirection = 'prev'
                let newSelected = curSelectedIndex - 1
                this.scrollToFile(this.$refs[`file_${newSelected}`])
            }
        },
        goToNext() {
            let curSelectedIndex = this.currentFileIndex

            if (curSelectedIndex < this.allItemsCount - 1) {
                this.navDirection = 'next'
                let newSelected = curSelectedIndex + 1
                this.scrollToFile(this.$refs[`file_${newSelected}`])
            }
        },
        scrollToFile(file) {
            file = file[0]
            file.click()

            let container = this.$refs['__stack-files'].$el
            let count = file.offsetTop - container.scrollTop - 20
            container.scrollBy({top: count, left: 0, behavior: 'smooth'})

            // when scrollBy() doesnt work
            if (!(container.scrollHeight > container.clientHeight)) {
                file.scrollIntoView({behavior: 'smooth', block: 'end', inline: 'end'})
            }
        }
    }
}
