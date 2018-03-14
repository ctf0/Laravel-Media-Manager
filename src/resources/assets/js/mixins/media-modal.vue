<script>
export default {
    name: 'image-modal',
    props: ['old', 'item', 'name', 'type'],
    mounted() {
        if (this.old) {
            this.$parent[this.item] = this.old
        }

        EventHub.listen('file_selected', (path) => {
            if (this.item == this.name && this.type !== 'folder') {
                this.$parent[this.item] = path
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
