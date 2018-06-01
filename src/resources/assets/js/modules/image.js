export default {
    methods: {
        // btns
        imageEditor() {
            if (this.selectedFileIs('image')) {
                this.toggleModal('imageEditor_modal')
            }
        },
        imageEditorCard() {
            this.toggleModal()

            // avoid flicker
            setTimeout(() => {
                this.imageEditor()
            }, 10)
        },

        // lazy
        lazyModeIsOn() {
            return this.config.lazyLoad
        },
        lazyImageActivate(url) {
            this.$nextTick(() => {
                EventHub.fire('lazy-image-activate', url)
            })
        },
        lazySelectFirst() {
            if (this.fileTypeIs(this.allFiles[0], 'folder')) {
                return this.selectFirst()
            }
        }
    }
}
