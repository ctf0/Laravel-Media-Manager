<template>
    <div class="__box-img">
        <img v-if="src"
             ref="img"
             :src="src"
             :alt="file.name"
             style="opacity: 0"
             async>
    </div>
</template>

<script>
export default {
    props: ['file', 'db', 'browserSupport'],
    data() {
        return {
            src: null,
            url: this.file.path
        }
    },
    created() {
        this.browserSupport('caches')
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
                    if (this.browserSupport('caches')) {
                        return this.cacheImageUrl(this.url)
                    }
                }
            })
        },
        showImg(url) {
            return this.src = url
        },
        sendDimensionsToParent() {
            const manager = this
            let img = this.$refs.img

            img.addEventListener('load', function() {
                if (this.naturalWidth <= 1500) {
                    img.style.objectFit = 'cover'
                }

                manager.$el.style.border = 'none'
                img.style.opacity = ''

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
                this.$nextTick(() => {
                    this.sendDimensionsToParent()
                })
            }
        }
    }
}
</script>
