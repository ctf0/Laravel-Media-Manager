<template>
    <div>
        <button v-tippy="{html: '#contentpopup2', interactive: true, trigger: 'click', theme: 'mm'}"
                :class="{'is-active': isUsed()}"
                :disabled="processing"
                class="btn-plain">
            {{ name }}
        </button>

        <div id="contentpopup2">
            <div class="level is-marginless">
                <transition name="mm-list">
                    <div v-show="range != 0" class="level-item">
                        <p class="heading is-marginless link" @click="resetFilter()">
                            <span class="icon"><icon name="times"/></span>
                        </p>
                    </div>
                </transition>

                <div class="level-item">
                    <p class="title is-5 is-marginless">{{ range }}</p>
                </div>
            </div>

            <input v-model.number="range"
                   :min="min"
                   :max="max"
                   :step="step"
                   :disabled="processing"
                   type="range">
        </div>
    </div>
</template>

<script>
import debounce from 'lodash/debounce'

export default {
    props: [
        'name',
        'max',
        'min',
        'step',
        'default',
        'processing',
        'distort'
    ],
    data() {
        return {
            range: this.default,
            wasReset: false
        }
    },
    beforeMount() {
        this.$options.name = `glitch-${this.name}`
    },
    mounted() {
        EventHub.listen('random-glitch', () => {
            this.randomizeValues()
        })

        EventHub.listen('reset-glitch', () => {
            this.wasReset = true
            this.resetFilter()
            setTimeout(() => {
                this.wasReset = false
            }, 50)
        })
    },
    methods: {
        resetFilter() {
            if (!this.processing) {
                this.range = 0
            }
        },
        randomizeValues() {
            this.range = parseInt(Math.random() * (this.max - this.min) + this.min, 10)
        },

        // check
        isUsed() {
            return this.range != 0
        },

        // send changes
        update(val) {
            this.distort(this.name, val)
        }
    },
    watch: {
        range(val) {
            if (!this.wasReset) {
                this.update(val)
            }
        }
    }
}
</script>
