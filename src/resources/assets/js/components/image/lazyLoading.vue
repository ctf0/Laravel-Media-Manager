<template>
    <div class="__box-img">
        <img v-if="src"
             ref="img"
             :src="src"
             :alt="file.name"
             :style="imgStyle"
             loading="lazy">
    </div>
</template>

<script>
import lazy from '../../mixins/lazy'

export default {
    mixins: [lazy],
    props: ['checkForDimensions'],
    data() {
        return {
            applyStyles: false
        }
    },
    computed: {
        imgStyle() {
            return this.applyStyles
                ? {
                    objectFit: 'cover',
                    opacity: ''
                }
                : {
                    opacity: 0
                }
        }
    },
    methods: {
        sendDimensionsToParent() {
            const manager = this
            let url = this.src

            this.$refs.img.addEventListener('load', function() {
                manager.applyStyles = true
                manager.$el.style.border = 'none'

                if (!manager.checkForDimensions(url)) {
                    EventHub.fire('save-image-dimensions', {
                        url: url,
                        val: `${this.naturalWidth} x ${this.naturalHeight}`
                    })
                }
            })

        }
    },
    watch: {
        intersected: {
            immediate: true,
            handler(val) {
                if (val) {
                    this.src = this.file.path
                    this.$nextTick(() => this.sendDimensionsToParent())
                }
            }
        }
    }
}
</script>
