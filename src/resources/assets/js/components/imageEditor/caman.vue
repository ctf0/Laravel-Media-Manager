<template>
    <div class="__caman">
        <button v-tippy :class="{'is-active': controls}"
                :disabled="processing"
                :title="filterName"
                class="btn-plain" @click="toggleControls()">
            <span class="icon"><icon :name="processing ? 'spinner' : icon" :pulse="processing"/></span>
        </button>

        <transition name="mm-list">
            <div v-show="controls" class="__caman-controls">
                <button :disabled="incLimit() || processing" class="btn-plain" @click="inc()">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'plus'" :pulse="processing"/></span>
                </button>
                <button :disabled="decLimit() || processing" class="btn-plain" @click="dec()">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'minus'" :pulse="processing"/></span>
                </button>
            </div>
        </transition>
    </div>
</template>

<script>
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
        toggleControls() {
            if (this.noController.includes(this.filterName)) {
                return this.update()
            }

            this.controls = !this.controls
        },

        inc() {
            this.range += this.step
            this.update(this.range)
        },
        dec() {
            this.range -= this.step
            this.update(this.range)
        },
        incLimit() {
            return this.range == this.max
        },
        decLimit() {
            return this.range == this.min
        },

        update(val = null) {
            this.$parent.updateFilter(this.filterName, val)
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
