<template>
    <div class="__box-img">
        <img v-if="src" ref="img" :src="src" :alt="file.name" async>
    </div>
</template>

<script>
import debounce from 'lodash/debounce'

export default {
    props: ['file', 'browserSupport'],
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
        if (this.browserSupport('IntersectionObserver') && this.observer) {
            this.observer.unobserve(this.$el)
            this.observer = null
        }
    },
    methods: {
        init() {
            // wait for any DOM stuff to finish
            this.$nextTick(debounce(() => {
                this.browserSupport('IntersectionObserver')
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
                root: document.querySelector('.__stack-files'),
                rootMargin: '0px',
                threshold: 1.0
            })

            if (this.$el) this.observer.observe(this.$el)
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
                this.$el.style.border = 'none'

                this.$nextTick(() => {
                    this.sendDimensionsToParent()
                })
            }
        }
    }
}
</script>
