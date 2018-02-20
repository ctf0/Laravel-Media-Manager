export default {
    props: ['old'],
    data() {
        return {
            showModal: false,
            selectedFile: '',
            selectedFolder: ''
        }
    },
    mounted() {
        if (this.old) {
            this.selectedFile = this.old
        }

        EventHub.listen('file_selected', (data) => {
            this.selectedFile = data
        })

        EventHub.listen('folder_selected', (data) => {
            this.selectedFolder = data
        })
    },
    methods: {
        toggleModal() {
            this.showModal = !this.showModal
        }
    },
    watch: {
        showModal(val) {
            if (val === false) {
                return EventHub.fire('modal-hide')
            }

            EventHub.fire('modal-show')
        }
    },
    render() {}
}
