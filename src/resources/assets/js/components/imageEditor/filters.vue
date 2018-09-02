<template>
    <div class="__caman">
        <button v-tippy="{arrow: true, theme: 'light'}" :class="{'is-active': controls}"
                :disabled="processing"
                :title="filterName"
                class="btn-plain" @click="toggleControls()">
            <span class="icon"><icon :name="processing ? 'spinner' : icon" :pulse="processing"/></span>
        </button>

        <transition name="mm-list">
            <div v-show="controls" class="__caman-controls">
                <button v-tippy="{arrow: true, hideOnClick: false, theme: 'light'}"
                        :disabled="incLimit() || processing"
                        :title="getTTc('inc')"
                        class="btn-plain"
                        @click="processing ? false : inc()">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'plus'" :pulse="processing"/></span>
                </button>
                <button v-tippy="{arrow: true, hideOnClick: false, theme: 'light'}"
                        :disabled="decLimit() || processing"
                        :title="getTTc('dec')"
                        class="btn-plain"
                        @click="processing ? false : dec()">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'minus'" :pulse="processing"/></span>
                </button>
            </div>
        </transition>
    </div>
</template>

<script>
import throttle from 'lodash/throttle'

export default {
    props: [
        'filterName',
        'icon',
        'step',
        'max',
        'min',
        'processing',
        'reset'
    ],
    data() {
        return {
            controls: false,
            noController: [
                'greyscale',
                'invert'
            ],
            range: 0
        }
    },
    beforeMount() {
        this.$options.name = `${this.filterName}-filter`
    },
    methods: {
        // helpers
        toggleControls() {
            if (this.noController.includes(this.filterName)) {
                return this.update()
            }

            this.controls = !this.controls
        },

        getTTc(s) {
            let c
            let range = this.getVal(this.range)
            let step = this.step

            if (s == 'dec') {
                if (!this.decLimit()) {
                    c = this.getVal(range - step)
                    return `${range} > ${c}`
                }

                return range
            }

            if (!this.incLimit()) {
                c = this.getVal(range + step)
                return `${range} > ${c}`
            }

            return range
        },
        getVal(val) {
            return val
        },

        // ops
        inc: throttle(function() {
            this.range += this.step
            this.update(this.range)
        }, 500),
        dec: throttle(function() {
            this.range -= this.step
            this.update(this.range)
        }, 500),

        // check
        incLimit() {
            return this.range == this.max
        },
        decLimit() {
            return this.range == this.min
        },

        // send changes
        update(val = null) {
            this.$parent.updateFilter(this.filterName, this.getVal(val))
        }
    },
    watch: {
        reset(val) {
            this.controls = false
            this.range = 0
        }
    }
}
</script>
