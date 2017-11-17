export default {
    watch: {
        allFiles(val) {
            if (val.length < 1) {
                this.noFiles('show')
            }
        },
        selectedFile(val) {
            if (!val || this.IsInLockedList(val)) {
                return this.toggleBtns('disable')
            }

            if (this.inModal && !this.selectedFileIs('folder')) {
                EventHub.fire('file_selected', val.path)
            }

            // hide move button when there is only one folder and its selected
            this.canWeMove()
            this.toggleBtns('enable')
        },

        // bulk
        bulkList(val) {
            if (val) {
                // hide move button when all folders are selected
                this.canWeMove()
            }

            if (val == 0 && this.isBulkSelecting()) {
                let btn = $('#blk_slct_all')

                if (btn.hasClass('is-warning')) {
                    btn.removeClass('is-warning')
                    btn.find('.fa').removeClass('fa-minus').addClass('fa-plus')
                    btn.find('span').not('.icon').text(this.trans('select_all'))
                }
            }
        },
        bulkItemsCount(val) {
            if (val > 1) {
                let btn = $('#blk_slct_all')

                if (!btn.hasClass('is-warning')) {
                    btn.addClass('is-warning')
                    btn.find('.fa').removeClass('fa-plus').addClass('fa-minus')
                    btn.find('span').not('.icon').text(this.trans('select_non'))
                }
            }
        },

        // filter
        showBy(val) {
            if (val) {
                if (val == 'clear') {
                    this.resetInput('showBy')
                }

                if (!this.isBulkSelecting()) {
                    this.selectFirst()
                }
            }
        },
        currentFilterName(val) {
            if (!val) {
                this.resetInput('filterdList', [])
            }
        },

        // search
        searchFor(val) {
            if (!this.isBulkSelecting()) {
                this.selectFirst()
            }

            if (!val) {
                this.resetInput('searchItemsCount')
                return this.noFiles('hide')
            }
        },
        searchItemsCount(val) {
            if (val == 0) {
                this.clearSelected()
            }

            if (this.allItemsCount == undefined) {
                this.resetInput('searchItemsCount')
            }
        }
    }
}
