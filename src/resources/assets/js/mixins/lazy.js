export default {
    props: ['file', 'browserSupport', 'rootEl'],
    data() {
        return {
            observer: null,
            src: null,
            intersected: false,
            isObserving: false
        }
    },
    mounted() {
        this.init()
    },
    beforeDestroy() {
        if (this.browserSupport('IntersectionObserver')) {
            this.stop()
        }
    },
    methods: {
        init() {
            if (!this.intersected) {
                this.browserSupport('IntersectionObserver')
                    ? this.observe()
                    : this.intersected = true
            }
        },
        stop() {
            if (this.observer) {
                this.isObserving = false
                this.observer.unobserve(this.$el)
                this.observer = null
            }
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
                root: document.querySelector(this.rootEl),
                threshold: 0.75
            })

            this.observer.observe(this.$el)
        }
    }
}
