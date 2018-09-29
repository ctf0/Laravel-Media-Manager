<template>
    <video :src="file.path" preload="metadata"/>
</template>

<script>
export default {
    props: ['file'],
    mounted() {
        this.$el.addEventListener('loadedmetadata', this.init)
    },
    beforeDestroy() {
        this.$el.removeEventListener('loadedmetadata', this.init)
    },
    methods: {
        init(e) {
            let t = e.target

            EventHub.fire('save-image-dimensions', {
                url: t.src,
                val: `${t.videoWidth} x ${t.videoHeight}`
            })
        }
    }
}
</script>
