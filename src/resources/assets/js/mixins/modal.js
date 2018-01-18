export default {
    props: ['old'],
    data() {
        return {
            showModal: false,
            selectedFile: ''
        }
    },
    mounted() {
        if (this.old) {
            this.selectedFile = this.old
        }

        EventHub.listen('file_selected', (data) => {
            this.selectedFile = data
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
