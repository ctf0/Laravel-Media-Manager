<template>
    <div class="__box-img">
        <img v-if="src" ref="img" :src="src" :alt="file.name" async>
    </div>
</template>

<script>
import lazy from '../../mixins/lazy'

export default {
    mixins: [lazy],
    methods: {
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
        intersected: {
            immediate: true,
            handler(val, oldVal) {
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
}
</script>
