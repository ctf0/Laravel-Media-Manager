import debounce from 'lodash/debounce'
import loader from '../wr'

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
                root: document.querySelector(this.rootEl),
                threshold: 0.75
            })

            this.observer.observe(this.$el)
        },
        fetchImg(url) {
            return loader(this.file.path)
        }
    }
}
