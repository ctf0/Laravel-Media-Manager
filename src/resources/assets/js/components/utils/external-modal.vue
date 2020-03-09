<script>
export default {
    name: 'image-modal',
    props: {
        'item': {
            type: String,
            required: true
        },
        'name': {
            type: String,
            required: true
        },
        'old': {
            type: String,
            required: false,
            default: ''
        },
        'type': {
            type: String,
            required: false,
            default: ''
        },
        'multi': {
            type: Boolean,
            required: false,
            default: false
        },
        'restrict': {
            type: Object,
            required: false,
            default: function () {
                return {}
            }
        }
    },
    created() {
        EventHub.fire('external_modal_resrtict', this.restrict)
    },
    mounted() {
        if (this.old) {
            this.updateParent(this.old)
        }

        EventHub.listen('file_selected', (path) => {
            if (this.item == this.name && this.type !== 'folder' && !this.multi) {
                this.updateParent(path)
            }
        })

        EventHub.listen('multi_file_selected', (paths) => {
            if (this.item == this.name && this.type !== 'folder' && this.multi) {
                this.updateParent(paths)
            }
        })

        EventHub.listen('folder_selected', (path) => {
            if (this.item == this.name && this.type == 'folder') {
                this.updateParent(path)
            }
        })
    },
    methods: {
        updateParent(path) {
            return this.$parent[this.item] = path
        }
    },
    render() {}
}
</script>
