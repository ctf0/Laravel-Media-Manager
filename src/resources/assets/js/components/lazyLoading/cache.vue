<template>
    <div ref="wrapper" class="__box-img">
        <img v-if="src" ref="img" :src="src" async>
    </div>
</template>

<script>
export default {
    props: ['url', 'db'],
    data() {
        return {
            src: null
        }
    },
    created() {
        'caches' in window
            ? this.getCachedUrl(this.url)
            : this.src = this.url
    },
    mounted() {
        this.init()
    },
    methods: {
        init() {
            EventHub.listen('lazy-image-activate', (url) => {
                if (url == this.url && !this.src) {
                    if ('caches' in window) {
                        return this.cacheImageUrl(this.url)
                    }
                }
            })
        },
        removePhBorder() {
            return this.$refs.wrapper.style.border = 'none'
        },
        showImg(url) {
            return this.src = url
        },
        sendDimensionsToParent() {
            const manager = this

            this.$refs.img.addEventListener('load', function() {
                EventHub.fire('save-image-dimensions', {
                    url: manager.url,
                    val: `${this.naturalWidth} x ${this.naturalHeight}`
                })
            })
        },

        // api
        cacheImageUrl(url) {
            return caches.open(this.db).then((cache) => {
                return cache.add(url).then(() => {
                    return this.getCachedUrl(url)
                }).catch((err) => {
                    this.showImg(url)
                    console.error(err)
                })
            }).catch((err) => {
                this.showImg(url)
                console.error(err)
            })
        },
        getCachedUrl(url) {
            return caches.open(this.db).then((cache) => {
                return cache.match(url).then((response) => {
                    return response ? response.blob() : null
                }).then((blob) => {
                    if (blob) {
                        return this.showImg(URL.createObjectURL(blob))
                    }
                })
            })
        }
    },
    watch: {
        src(val) {
            if (val) {
                this.removePhBorder()

                this.$nextTick(() => {
                    this.sendDimensionsToParent()
                })
            }
        }
    }
}
</script>
