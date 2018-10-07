import {loadImageWithWorker} from '../webworker'

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
            EventHub.listen('start-img-observing', () => {
                this.isObserving = true

                setTimeout(() => {
                    if (!this.intersected) {
                        this.browserSupport('IntersectionObserver')
                            ? this.observe()
                            : this.intersected = true
                    }
                }, 500)
            })

            EventHub.listen('start-search-observing', () => {
                this.isObserving = true

                setTimeout(() => {
                    this.browserSupport('IntersectionObserver')
                        ? this.observe()
                        : this.intersected = true
                }, 500)
            })

            EventHub.listen('stop-img-observing', () => {
                this.stop()
            })
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
        },
        fetchImg() {
            return loadImageWithWorker(this.file.path)
        }
    }
}
