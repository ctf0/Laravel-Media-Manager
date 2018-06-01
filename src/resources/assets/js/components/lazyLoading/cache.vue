<template>
    <div ref="item" class="__box-img">
        <img v-if="cached" :src="imgData" async>
        <img v-else-if="normal" :src="url" async>
    </div>
</template>

<script>
export default {
    props: ['file', 'index', 'db'],
    data() {
        return {
            imgData: null,
            normal: false,
            url: this.file.path
        }
    },
    created() {
        if ('caches' in window) {
            this.getCachedUrl(this.url)
        } else {
            this.normal = true
        }
    },
    mounted() {
        EventHub.listen('lazy-image-activate', (url) => {
            if (url == this.url && !this.cached && !this.normal) {
                if ('caches' in window) {
                    return this.cacheImageUrl(this.url)
                }
            }
        })
    },
    computed: {
        cached() {
            return Boolean(this.imgData)
        }
    },
    methods: {
        removePhBorder() {
            return this.$refs.item.style.border = 'none'
        },

        // api
        cacheImageUrl(url) {
            return caches.open(this.db).then((cache) => {
                return cache.add(url).then(() => {
                    return this.getCachedUrl(url)
                }).catch((err) => {
                    this.normal = true
                    console.error(err)
                })
            }).catch((err) => {
                console.error(err)
            })
        },
        getCachedUrl(url) {
            return caches.open(this.db).then((cache) => {
                return cache.match(url).then((response) => {
                    return response ? response.blob() : null
                }).then((blob) => {
                    if (blob) {
                        return this.imgData = URL.createObjectURL(blob)
                    }
                })
            })
        }
    },
    watch: {
        cached(val) {
            if (val) {
                this.removePhBorder()
            }
        },
        normal(val) {
            if (val) {
                this.removePhBorder()
            }
        }
    }
}
</script>
