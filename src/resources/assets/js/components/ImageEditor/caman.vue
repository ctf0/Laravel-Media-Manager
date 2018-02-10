<template>
    <div class="__caman">
        <button class="btn-plain" @click="toggleControls()"
                :class="{'is-active': controls}"
                :disabled="processing"
                v-tippy :title="filterName">
            <span class="icon"><icon :name="processing ? 'spinner' : icon" :pulse="processing"/></span>
        </button>

        <transition name="list">
            <div v-show="controls" class="__caman-controls">
                <button class="btn-plain" :disabled="incLimit() || processing" @click="inc()">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'plus'" :pulse="processing"/></span>
                </button>
                <button class="btn-plain" :disabled="decLimit() || processing" @click="dec()">
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
