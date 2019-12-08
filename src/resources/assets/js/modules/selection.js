export default {
    methods: {
        /*                Item                */
        selectFirst(i = 0) {
            this.$nextTick(() => this.scrollToFile(this.getElementByIndex(i)))
        },
        isSelected(file) {
            return this.selectedFile == file
        },
        setSelected(file, index, e = null) {
            if (!this.isBulkSelecting() && this.isSelected(file)) return

            // EventHub.fire('stopHammerPropagate')

            if (e && e.metaKey && !this.firstMeta) {
                this.firstMeta = true
                this.bulkSelect = true
                this.pushtoBulkList(this.selectedFile)
            }

            // select with shift
            if (e && e.shiftKey) {
                this.bulkSelect = true
                this.resetInput('bulkList', [])

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

                let indexList = this.getIndexRange(begin, end)

                indexList.map((i) => {
                    this.getElementByIndex(i).click()
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

            // normal selection
            this.selectedFile = file
            this.currentFileIndex = index

            // bulk selection
            if (this.isBulkSelecting()) {
                this.pushtoBulkList(file)
            }
        },
        selectedFileIs(val) {
            let selected = this.selectedFile

            if (selected) {
                return this.fileTypeIs(selected, val)
            }
        },
        textFileType() {
            // dont open files like "rtf" because browser cant read them, only open "txt"
            return this.selectedFileIs('text/plain')
        },

        /*                Navigation                */
        navigation(e) {
            let key = keycode(e)

            // go to prev item
            if (key == 'left') {
                e.preventDefault()
                this.goToPrev()
            }

            // go to prev row
            if (key == 'up') {
                e.preventDefault()
                this.goToPrevRow()
            }

            // go to next item
            if (key == 'right') {
                e.preventDefault()
                this.goToNext()
            }

            // go to next row
            if (key == 'down') {
                e.preventDefault()
                this.goToNextRow()
            }

            // go to last item
            if (key == 'end') {
                e.preventDefault()
                this.goToEnd()
            }

            // go to first item
            if (key == 'home') {
                e.preventDefault()
                this.goToHome()
            }

            // toggle modal off
            this.checkTypeB4Navigation()
        },
        checkTypeB4Navigation() {
            if (
                this.isActiveModal('preview_modal') &&
                (this.selectedFileIs('text') && !this.textFileType()) ||
                (this.selectedFileIs('folder') || this.selectedFileIs('application') || this.selectedFileIs('compressed')) ||
                (this.selectedFileIs('video') || this.selectedFileIs('audio')) && this.infoSidebar
            ) {
                return this.toggleModal()
            }
        },

        // items
        goToNext() {
            let curSelectedIndex = this.currentFileIndex

            if (curSelectedIndex == this.allItemsCount - 1) {
                this.toggleModal()
            }

            if (curSelectedIndex < this.allItemsCount - 1) {
                this.imageSlideDirection = 'nxt'
                this.scrollToFile(this.getElementByIndex(curSelectedIndex + 1))
            }
        },
        goToPrev() {
            let curSelectedIndex = this.currentFileIndex

            if (curSelectedIndex !== 0) {
                this.imageSlideDirection = 'prv'
                this.scrollToFile(this.getElementByIndex(curSelectedIndex - 1))
            } else {
                this.toggleModal()
            }
        },
        goToHome() {
            this.imageSlideDirection = 'dwn'
            this.scrollToFile(this.getElementByIndex(0))
        },
        goToEnd() {
            this.imageSlideDirection = 'up'
            this.scrollToFile(this.getElementByIndex(this.filesList.length - 1))
        },

        // rows
        goToNextRow() {
            let curSelectedIndex = this.currentFileIndex
            let moveBy = this.scrollByRowItemsCount
            this.imageSlideDirection = 'up'

            if (curSelectedIndex == this.allItemsCount - 1) {
                this.toggleModal()
            }

            if (curSelectedIndex < this.allItemsCount - moveBy) {
                this.scrollToFile(this.getElementByIndex(curSelectedIndex + moveBy))
            } else {
                this.goToEnd()
            }
        },
        goToPrevRow() {
            let curSelectedIndex = this.currentFileIndex
            let moveBy = this.scrollByRowItemsCount

            if (curSelectedIndex == 0) {
                this.toggleModal()
            }

            if (curSelectedIndex >= moveBy) {
                this.imageSlideDirection = 'dwn'
                this.scrollToFile(this.getElementByIndex(curSelectedIndex - moveBy))
            }
        }
    }
}
