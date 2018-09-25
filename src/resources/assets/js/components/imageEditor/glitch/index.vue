<template>
    <div>
        <controls v-for="item in optionsList"
                  :key="item.name"
                  :min="item.min"
                  :max="item.max"
                  :step="item.step"
                  :name="item.name"
                  :default="item.default"
                  :processing="processing"
                  :distort="distort"
                  class="__caman-filters"/>

        <div class="__caman-filters">
            <button :disabled="processing" class="btn-plain" @click="random()">randomize</button>
        </div>
    </div>
</template>

<script>
const defaults = {
    amount: 0,
    iterations: 0,
    quality: 1,
    seed: 0
}

export default {
    components: {
        controls: require('./controls.vue')
    },
    props: [
        'cropper',
        'getCropperData'
    ],
    data() {
        return {
            optionsList: [
                {
                    step: 1,
                    min: 0,
                    max: 99,
                    default: defaults.amount,
                    name: 'amount'
                },
                {
                    step: 1,
                    min: 0,
                    max: 100,
                    default: defaults.seed,
                    name: 'seed'
                },
                {
                    step: 1,
                    min: 0,
                    max: 100,
                    default: defaults.iterations,
                    name: 'iterations'
                },
                {
                    step: 1,
                    min: 1,
                    max: 99,
                    default: defaults.quality,
                    name: 'quality'
                }
            ],
            init: null,
            options: defaults,
            processing: false,
            lib: null
        }
    },
    mounted() {
        new Promise((resolve) => {
            this.init = this.getCropperData()
            return resolve()
        }).then(() => {
            this.distort()
        })

        EventHub.listen('reset-glitch', () => {
            this.cropper.replace(this.init, true)
        })
    },
    methods: {
        distort(name = null, val = null) {
            this.processing = true
            let img = new Image()

            if (name && val) {
                this.options[name] = val
            }

            this.$nextTick(() => {
                img.onload = () => {
                    glitch(this.options)
                        .fromImage(img)
                        .toDataURL()
                        .then((dataURL) => {
                            this.cropper.replace(dataURL, true)
                            this.processing = false
                            this.$parent.hasChanged = true
                        })
                }

                img.src = this.init
            })
        },
        random() {
            EventHub.fire('random-glitch')
        }
    }
}
</script>
