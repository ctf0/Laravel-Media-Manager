import MediaModal from './media-modal.vue'

export default {
    components: {MediaModal},
    data() {
        return {
            inputName: ''
        }
    },
    methods: {
        toggleModalFor(e, name) {
            if (e.altKey) {
                return this.removeImg(name)
            }

            this.inputName = name
            EventHub.fire('modal-show')
        },
        hideInputModal() {
            this.inputName = ''
            EventHub.fire('modal-hide')
        },
        removeImg(name) {
            this[name] = ''
        }
    }
}
