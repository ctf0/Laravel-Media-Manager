<template>
    <div ref="wrapper" class="__box-img">
        <img v-if="src" ref="img" :src="src" async>
    </div>
</template>

<script>
import debounce from 'lodash/debounce'

export default {
    props: ['url'],
    data() {
        return {
            observer: null,
            src: null,
            intersected: false
        }
    },
    mounted() {
        this.init()
    },
    beforeDestroy() {
        if ('IntersectionObserver' in window) {
            this.observer.unobserve(this.$refs.wrapper)
            this.observer = null
        }
    },
    methods: {
        init() {
            this.$nextTick(debounce(() => {
                if ('IntersectionObserver' in window) {
                    this.observe()
                } else {
                    this.intersected = true
                }
            }, 250))
        },
        observe() {
            this.observer = new IntersectionObserver((item, observer) => {
                item.forEach((img) => {
                    if (img.isIntersecting) {
                        this.intersected = true
                        observer.unobserve(img.target)
                    }
                })
            })

            this.observer.observe(this.$refs.wrapper)
        },
        sendDimensionsToParent() {
            const manager = this

            this.$refs.img.addEventListener('load', function() {
                EventHub.fire('save-image-dimensions', {
                    url: manager.url,
                    val: `${this.naturalWidth} x ${this.naturalHeight}`
                })
            })
        }
    },
    watch: {
        intersected(val) {
            if (val) {
                this.src = this.url
                this.$refs.wrapper.style.border = 'none'

                this.$nextTick(() => {
                    this.sendDimensionsToParent()
                })
            }
        }
    }
}
</script>
