<template>
    <div ref="wrapper" class="__box-img">
        <img v-if="src" ref="img" :src="src" :alt="file.name" async>
    </div>
</template>

<script>
import debounce from 'lodash/debounce'

export default {
    props: ['file'],
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
        if ('IntersectionObserver' in window && this.observer) {
            this.observer.unobserve(this.$refs.wrapper)
            this.observer = null
        }
    },
    methods: {
        init() {
            // wait for any DOM stuff to finish
            this.$nextTick(debounce(() => {
                'IntersectionObserver' in window
                    ? this.observe()
                    : this.intersected = true
            }, 500))
        },
        observe() {
            this.observer = new IntersectionObserver((item, observer) => {
                item.forEach((img) => {
                    if (img.isIntersecting) {
                        this.intersected = true
                        observer.unobserve(img.target)
                    }
                })
            }, {
                root: document.querySelector('.media-manager__stack-files'),
                rootMargin: '0px',
                threshold: 1.0
            })

            if (this.$refs.wrapper) this.observer.observe(this.$refs.wrapper)
        },
        sendDimensionsToParent() {
            const manager = this

            this.$refs.img.addEventListener('load', function() {
                EventHub.fire('save-image-dimensions', {
                    url: manager.file.path,
                    val: `${this.naturalWidth} x ${this.naturalHeight}`
                })
            })
        }
    },
    watch: {
        intersected(val) {
            if (val) {
                this.src = this.file.path
                this.$refs.wrapper.style.border = 'none'

                this.$nextTick(() => {
                    this.sendDimensionsToParent()
                })
            }
        }
    }
}
</script>
