export default {
    methods: {
        selectedFileIs(val) {
            if (typeof this.selectedFile !== 'undefined') {
                return this.fileTypeIs(this.selectedFile, val)
            }
        },
        setSelected(file) {
            this.clearSelected()
            $('div[data-folder="' + file.name + '"]').addClass('selected')
            this.selectedFile = file

            if (this.isBulkSelecting()) {
                this.pushtoBulkList(file)
            }
        },
        clearSelected() {
            this.resetInput('selectedFile')
            $('#files li .selected').removeClass('selected')
        },
        selectFirst() {
            this.$nextTick(() => {
                let file = $('div[data-index="0"]')
                if (file.length > 0) {
                    file.trigger('click')
                }
            })
        },
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

                this.folders = this.folders.splice(0, index)
                this.getFiles(this.folders)

                this.resetInput('currentFilterName')
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
