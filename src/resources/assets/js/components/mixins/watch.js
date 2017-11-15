export default {
    watch: {
        allFiles(val) {
            if (val.length < 1) {
                this.resetInput('selectedFile')
                return this.noFiles('show')
            }

            this.noFiles('hide')
        },
        bulkList(val) {
            if (val) {
                // hide move button when all folders are selected
                this.$nextTick(() => {
                    if (!this.checkForFolders()) {
                        $('#move').attr('disabled', true)
                    }
                })
            }

            if (val == 0 && this.isBulkSelecting()) {
                let toggle_text = $('#blk_slct_all').find('span').not('.icon')
                $('#blk_slct_all').removeClass('is-warning')
                $('#blk_slct_all').find('.fa').removeClass('fa-minus').addClass('fa-plus')
                toggle_text.text(this.trans.all)
            }
        },
        selectedFile(val) {
            if (!val) {
                $('#move').attr('disabled', true)
                $('#rename').attr('disabled', true)
                $('#delete').attr('disabled', true)
            } else {
                // hide move button when there is only one folder and its selected
                this.$nextTick(() => {
                    if (!this.checkForFolders()) {
                        $('#move').attr('disabled', true)
                    }
                })

                $('#move').removeAttr('disabled')
                $('#rename').removeAttr('disabled')
                $('#delete').removeAttr('disabled')
            }
        },
        searchFor(val) {
            if (!val) {
                this.resetInput('searchItemsCount')
                this.noFiles('hide')
            } else {
                this.updateSearchCount()

                // so we dont miss with the bulk selection list
                if (!this.isBulkSelecting()) {
                    this.clearSelected()
                }
            }

            if (!this.isBulkSelecting()) {
                this.selectFirst()
            }
        },
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
                this.filterdList = []
            }
        }
    }
}
