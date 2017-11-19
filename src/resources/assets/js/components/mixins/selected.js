export default {
    methods: {
        /*                Selected                */
        getSelected() {
            return $('#files li .selected')
        },
        clearSelected() {
            this.resetInput('selectedFile')
            this.getSelected().removeClass('selected bulk-selected')
        },
        selectFirst() {
            this.$nextTick(() => {
                let file = $('div[data-index="0"]')

                if (file.length > 0) {
                    file.trigger('click')
                }
            })
        },
        setSelected(file) {
            this.clearSelected()
            this.selectedFile = file
            $('div[data-item="' + file.name + '"]').addClass('selected')

            if (this.isBulkSelecting()) {
                this.pushtoBulkList(file)
            }

        },
        selectedFileIs(val) {
            if (typeof this.selectedFile !== 'undefined') {
                return this.fileTypeIs(this.selectedFile, val)
            }
        },

        /*                Folder                */
        openFolder(file) {
            if (!this.isBulkSelecting()) {
                if (!this.fileTypeIs(file, 'folder')) {
                    return false
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
                    return false
                }

                let prev_folder_name = this.folders[index]

                this.folders = this.folders.splice(0, index)
                this.getFiles(this.folders, prev_folder_name)
            }
        },
        goToPrevFolder() {
            let newSelected = parseInt(this.folders.length) - 1

            if (newSelected < 0) {
                return false
            }

            this.goToFolder(newSelected)
        },

        /*                Navigation                */
        navigation(e) {
            // go to prev image
            if (keycode(e) == 'left' || keycode(e) == 'up') {
                e.preventDefault()
                this.goToPrev()
            }

            // go to next image
            if (keycode(e) == 'right' || keycode(e) == 'down') {
                e.preventDefault()
                this.goToNext()
            }

            // go to last item
            if (keycode(e) == 'end') {
                e.preventDefault()

                let newSelected = this.allItemsCount - 1
                let cur = $('div[data-index="' + newSelected + '"]')
                this.scrollToFile(cur)
            }

            // go to first item
            if (keycode(e) == 'home') {
                e.preventDefault()
                this.scrollToFile()
            }

            if (this.lightBoxIsActive() && !this.selectedFileIs('image')) {
                this.toggleModal()
            }
        },
        goToPrev() {
            let curSelectedIndex = parseInt(this.getSelected().data('index'))

            if (curSelectedIndex !== 0) {
                let newSelected = curSelectedIndex - 1
                let cur = $('div[data-index="' + newSelected + '"]')
                this.scrollToFile(cur)
            }
        },
        goToNext() {
            let curSelectedIndex = parseInt(this.getSelected().data('index'))

            if (curSelectedIndex < this.allItemsCount - 1) {
                let newSelected = curSelectedIndex + 1
                let cur = $('div[data-index="' + newSelected + '"]')
                this.scrollToFile(cur)
            }
        },
        scrollToFile(file) {
            if (!file) {
                file = $('div[data-index="0"]')
            }

            let container = $('#left')
            let offset = parseInt(container.css('padding-top')) + parseInt(file.css('margin-top'))

            file.trigger('click')
            file[0].scrollIntoView(false)

            // respect container & file offset when scrolling
            if (file[0].offsetTop > container.height()) {
                container[0].scrollTop += offset
            }
        }
    }
}
