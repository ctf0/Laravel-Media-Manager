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
        lazyImageActive(item) {
            this.$nextTick(() => {
                let img = this.$refs[item]

                if (img && img.length && img[0].dataset.src) {
                    img = img[0]
                    img.src = img.dataset.src
                    img.removeAttribute('data-src')
                    img.parentElement.style.border = 'none'
                }
            })
        },
        imageIsCached(url) {
            // TODO
            return false
        }
    }
}