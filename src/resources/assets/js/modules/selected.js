export default {
    methods: {
        /*                Selected                */
        selectFirst() {
            this.$nextTick(() => {
                let file = this.$refs.file_0[0]

                if (file) {
                    file.$el.click()
                }
            })
        },
        setSelected(file, index, e = null) {
            // select with shift
            if (e && e.shiftKey) {
                this.bulkSelect = true

                // forward
                let begin = this.currentFileIndex
                let end = index
                let dir = 'forward'

                // backward
                if (begin > index) {
                    begin = index
                    end = this.currentFileIndex
                    dir = 'backward'
                }

                // search
                if (this.searchFor) {
                    this.bulkList = []
                    let indexList = this.getRange(begin, end)

                    indexList.map((i) => {
                        this.$refs[`file_${i}`][0].$el.click()
                    })

                    // to have the same expected pattern as normal shift + click
                    if (dir == 'forward') {
                        this.selectedFile = this.bulkList[0]
                        this.currentFileIndex = indexList[0]
                    } else {
                        this.selectedFile = this.bulkList[this.bulkItemsCount - 1]
                        this.currentFileIndex = indexList[indexList.length - 1]
                    }

                    return
                }

                // default
                return this.bulkList = this.allFiles.slice(begin, end + 1)
            }

            // normal selection
            this.selectedFile = file
            this.currentFileIndex = index
            this.lazyImageActive(file.path)

            // bulk selection
            if (this.isBulkSelecting()) {
                this.pushtoBulkList(file)
            }
        },
        getRange(start, end) {
            return Array(end - start + 1).fill().map((_, idx) => start + idx)
        },
        selectedFileIs(val) {
            if (this.selectedFile !== null) {
                return this.fileTypeIs(this.selectedFile, val)
            }
        },
        dbltap() {
            if (!this.isBulkSelecting()) {
                return this.selectedFileIs('image') || this.selectedFileIs('pdf') || this.selectedFileIs('text')
                    ? this.toggleModal('preview_modal')
                    : this.openFolder(this.selectedFile)
            }
        },

        /*                Folder                */
        openFolder(file) {
            if (!this.isBulkSelecting() && this.fileTypeIs(file, 'folder')) {
                this.folders.push(file.name)
                this.getFiles(this.folders)
            }

            this.resetInput('currentFilterName')
        },
        goToFolder(index) {
            if (!this.isBulkSelecting()) {
                this.noFiles('hide')
                this.resetInput('currentFilterName')

                let prev_folder_name = this.folders[index]

                this.folders = this.folders.splice(0, index)
                this.getFiles(this.folders, prev_folder_name)
            }
        },
        goToPrevFolder() {
            let length = this.folders.length
            let newSelected = length - 1

            return length == 0
                ? false
                : this.goToFolder(newSelected)
        },

        /*                Navigation                */
        navigation(e) {
            let key = keycode(e)

            // go to prev item
            if (key == 'left' || key == 'up') {
                e.preventDefault()
                this.goToPrev()
            }

            // go to next item
            if (key == 'right' || key == 'down') {
                e.preventDefault()
                this.goToNext()
            }

            // go to last item
            if (key == 'end') {
                e.preventDefault()
                this.imageSlideDirection = 'next'

                let last = this.filesList.length - 1
                this.scrollToFile(this.$refs[`file_${last}`])
            }

            // go to first item
            if (key == 'home') {
                e.preventDefault()
                this.imageSlideDirection = 'prev'
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
                this.imageSlideDirection = 'prev'
                this.scrollToFile(this.$refs[`file_${curSelectedIndex - 1}`])
            }
        },
        goToNext() {
            let curSelectedIndex = this.currentFileIndex

            if (curSelectedIndex < this.allItemsCount - 1) {
                this.imageSlideDirection = 'next'
                this.scrollToFile(this.$refs[`file_${curSelectedIndex + 1}`])
            }
        },
        scrollToFile(file) {
            file = file[0].$el
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
