<template>
    <div v-tippy="{arrow: true, theme: 'mm'}"
         :title="getTitle(filterName)">
        <section v-if="!isControlable">
            <button v-tippy="{html: '#contentpopup2', interactive: true, reactive: true, trigger: 'click', theme: 'mm', arrow: true}"
                    :class="{'is-active': isUsed()}"
                    :disabled="processing"
                    class="btn-plain">
                <span class="icon">
                    <icon :name="processing ? 'spinner' : icon"
                          :pulse="processing"/>
                </span>
            </button>

            <div id="contentpopup2">
                <div class="level is-marginless">
                    <transition name="mm-list">
                        <div v-show="range != 0"
                             class="level-item">
                            <p class="heading is-marginless link"
                               @click.stop="resetFilter()">
                                <span class="icon">
                                    <icon name="times"/>
                                </span>
                            </p>
                        </div>
                    </transition>

                    <div class="level-item">
                        <p class="title is-5 is-marginless">
                            {{ range }}
                        </p>
                    </div>
                </div>

                <input ref="range"
                       v-model.number="range"
                       :min="min"
                       :max="max"
                       :step="step"
                       :disabled="processing"
                       type="range">
            </div>
        </section>

        <button v-else
                :class="{'is-active': isUsed()}"
                :disabled="processing"
                class="btn-plain"
                @click.stop="update()">
            <span class="icon">
                <icon :name="processing ? 'spinner' : icon"
                      :pulse="processing"/>
            </span>
        </button>
    </div>
</template>

<script>
import debounce  from 'lodash/debounce'
import snakeCase from 'lodash/snakeCase'

export default {
    props: [
        'trans',
        'filterName',
        'icon',
        'max',
        'min',
        'step',
        'reset',
        'processing',
        'applyFilter',
        'noController',
        'camanFilters'
    ],
    data() {
        return {
            range   : 0,
            wasReset: false
        }
    },
    computed: {
        isControlable() {
            return this.noController.includes(this.filterName)
        }
    },
    beforeMount() {
        this.$options.name = `${this.filterName}-filter`
    },
    methods: {
        resetFilter() {
            if (!this.processing) {
                this.range = 0
                if (this.$refs.range) {
                    this.$refs.range.style.setProperty('--length', '')
                    this.$refs.range.classList.remove('range-neg')
                    this.$refs.range.classList.remove('range-pos')
                }
            }
        },
        isUsed() {
            return this.camanFilters.hasOwnProperty(this.filterName)
        },
        updateStyles(val) {
            let item = this.$refs.range
            let min = this.min
            let max = this.max
            let total = max - min
            let perc

            // - > +
            if (min < 0) {
                // - < 0
                if (val < 0) {
                    item.classList.remove('range-pos')
                    perc = Math.abs(parseFloat(
                        ((min - val) * 100 / total).toFixed(2)
                    )) + '%'
                    item.style.setProperty('--length', perc)

                    return item.classList.add('range-neg')
                }
                // 0 > +
                else {
                    let calc = total / 2 + val

                    item.classList.remove('range-neg')
                    perc = parseFloat(
                        (calc * 100 / total).toFixed(2)
                    ) + '%'
                    item.style.setProperty('--length', perc)

                    return item.classList.add('range-pos')
                }
            }
            // 0 > +
            else {
                perc = parseFloat((val * 100 / max).toFixed(2)) + '%'
                this.$refs.range.style.setProperty('--length', perc)
            }
        },
        getTitle(str) {
            return this.trans(snakeCase(str))
        },
        update: debounce(function(val = null) {
            this.applyFilter(this.filterName, val)
        }, 500)
    },
    watch: {
        reset(val) {
            if (val) {
                this.wasReset = true
                this.resetFilter()
                this.$nextTick(() => this.wasReset = false)
            }
        },
        range(val) {
            if (!this.wasReset) {
                this.update(val)
                this.updateStyles(val)
            }
        }
    }
}
</script>
