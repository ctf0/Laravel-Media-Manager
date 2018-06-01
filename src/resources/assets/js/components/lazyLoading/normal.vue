<template>
    <div ref="item" class="__box-img">
        <img v-if="intersected" :src="srcImage" async>
    </div>
</template>

<script>
import debounce from 'lodash/debounce'

export default {
    props: ['file'],
    data() {
        return {
            observer: null,
            intersected: false
        }
    },
    mounted() {
        this.$nextTick(debounce(() => {
            if ('IntersectionObserver' in window) {
                this.observe()
            } else {
                this.intersected = true
            }
        }, 250))
    },
    beforeDestroy() {
        if ('IntersectionObserver' in window && this.observer) {
            this.observer.unobserve(this.$refs.item)
            this.observer = null
        }
    },
    computed: {
        srcImage() {
            return this.intersected
                ? this.file.path
                : ''
        }
    },
    methods: {
        observe() {
            this.observer = new IntersectionObserver((item, observer) => {
                item.forEach((img) => {
                    if (img.isIntersecting) {
                        this.intersected = true
                        observer.unobserve(img.target)
                    }
                })
            })

            this.observer.observe(this.$refs.item)
        }
    },
    watch: {
        intersected(val) {
            if (val) {
                this.$refs.item.style.border = 'none'
            }
        }
    }
}
</script>
