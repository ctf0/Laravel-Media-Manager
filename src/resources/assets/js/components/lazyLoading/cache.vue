<template>
    <div ref="wrapper" class="__box-img-lazy">
        <div v-if="cached" :style="{'--imageSrc': `url('${imgData}')`}" class="__box-img"/>
        <div v-if="normal" :style="{'--imageSrc': `url('${url}')`}" class="__box-img"/>
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
        EventHub.listen('lazy-image-activate', (i) => {
            if (i == this.index && !this.cached && !this.normal) {
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
            return this.$refs.wrapper ? this.$refs.wrapper.style.border = 'none' : false
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
