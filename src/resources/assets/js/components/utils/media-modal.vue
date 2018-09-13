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
        }
    },
    mounted() {
        if (this.old) {
            this.$parent[this.item] = this.old
        }

        EventHub.listen('file_selected', (path) => {
            if (this.item == this.name && this.type !== 'folder' && !this.multi) {
                this.$parent[this.item] = path
            }
        })

        EventHub.listen('multi_file_selected', (paths) => {
            if (this.item == this.name && this.type !== 'folder' && this.multi) {
                this.$parent[this.item] = paths
            }
        })

        EventHub.listen('folder_selected', (path) => {
            if (this.item == this.name && this.type == 'folder') {
                this.$parent[this.item] = path
            }
        })
    },
    render() {}
}
</script>
