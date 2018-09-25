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
import lazy from '../../mixins/lazy'

export default {
    mixins: [lazy],
    methods: {
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
                    this.fetchImg().then((img) => {
                        this.src = img

                        this.$nextTick(() => {
                            this.sendDimensionsToParent()
                        })
                    })
                }
            }
        }
    }
}
</script>
