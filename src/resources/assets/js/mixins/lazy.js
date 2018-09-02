import debounce from 'lodash/debounce'

export default {
    props: ['file', 'browserSupport', 'rootEl'],
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
                // root: document.querySelector(this.rootEl),
                threshold: 1.0
            })

            if (this.$el) this.observer.observe(this.$el)
        }
    },
    watch: {
        intersected: {
            immediate: true,
            handler(val, oldVal) {
                if (val) {
                    this.src = this.file.path
                }
            }
        }
    }
}
