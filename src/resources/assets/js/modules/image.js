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
            setTimeout(this.imageEditor, 10)
        },

        // dimensions
        checkForDimensions(url) {
            return this.dimensions.some((e) => e.url == url)
        },
        saveVideoDimensions(e) {
            let t = e.target
            let url = t.src.replace(/%20/g, ' ') // because browser convert 'spaces' to '%20'

            if (!this.checkForDimensions(url)) {
                EventHub.fire('save-image-dimensions', {
                    url: url,
                    val: `${t.videoWidth} x ${t.videoHeight}`
                })
            }
        }
    },
    computed: {
        selectedFileDimensions() {
            let f = this.dimensions.find((e) => e.url == this.selectedFile.path)

            return f ? f.val : '...'
        }
    }
}
