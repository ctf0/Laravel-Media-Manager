<template>
    <div ref="wrapper" class="__box-img-lazy">
        <img :src="file.path" style="display:none;">
        <div ref="item" :style="{'--imageSrc': `url('${srcImage}')`}" class="__box-img"/>
    </div>
</template>

<script>
export default {
    props: ['file'],
    data() {
        return {
            observer: null,
            intersected: false
        }
    },
    mounted() {
        // wait for the animation to stop = 50ms
        // wait for the sidebar to showup = 250ms
        setTimeout(() => {
            if ('IntersectionObserver' in window) {
                this.observe()
            } else {
                this.intersected = true
            }
        }, 300)
    },
    beforeDestroy() {
        if ('IntersectionObserver' in window && this.observer) {
            this.observer.disconnect()
        }
    },
    computed: {
        srcImage() {
            return this.intersected ? this.file.path : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
        }
    },
    methods: {
        observe() {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach((img) => {
                    if (img.isIntersecting) {
                        this.intersected = true
                        this.observer.disconnect()
                    }
                })
            })

            this.$refs.item ? this.observer.observe(this.$refs.item) : false
        }
    },
    watch: {
        intersected(val) {
            if (val) {
                return this.$refs.wrapper ? this.$refs.wrapper.style.border = 'none' : false
            }
        }
    }
}
</script>
