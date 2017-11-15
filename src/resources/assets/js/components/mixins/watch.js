export default {
    watch: {
        allFiles(val) {
            if (val.length < 1) {
                this.noFiles('show')
            }
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
                toggle_text.text(this.trans('select_all'))
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
            if (!this.isBulkSelecting()) {
                this.selectFirst()
            }

            if (!val) {
                this.resetInput('searchItemsCount')
                return this.noFiles('hide')
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
                this.resetInput('filterdList', [])
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
