<template>
    <div>
        <controls v-for="item in filtersList"
                  :key="item.filterName"
                  :min="item.min"
                  :max="item.max"
                  :step="item.step"
                  :icon="item.icon"
                  :filter-name="item.filterName"
                  :trans="trans"
                  :reset="reset"
                  :apply-filter="applyFilter"
                  :processing="processing"
                  :caman-filters="camanFilters"
                  :no-controller="noController"
                  class="__caman-filters"/>
    </div>
</template>

<script>
export default {
    components: {
        controls: require('./controls.vue').default
    },
    props: [
        'processing',
        'trans',
        'reset',
        'applyFilter',
        'camanFilters'
    ],
    data() {
        return {
            filtersList: [
                {
                    step       : 1,
                    min        : -100,
                    max        : 100,
                    icon       : 'regular/sun',
                    filterName : 'brightness'
                },
                {
                    step       : 1,
                    min        : -100,
                    max        : 100,
                    icon       : 'adjust',
                    filterName : 'contrast'
                },
                {
                    step       : 1,
                    min        : -100,
                    max        : 100,
                    icon       : 'low-vision',
                    filterName : 'saturation'
                },
                {
                    step       : 1,
                    min        : -100,
                    max        : 100,
                    icon       : 'bolt',
                    filterName : 'vibrance'
                },
                {
                    step       : 1,
                    min        : -100,
                    max        : 100,
                    icon       : 'thermometer-half',
                    filterName : 'exposure'
                },
                {
                    step       : 1,
                    min        : 0,
                    max        : 100,
                    icon       : 'eye-dropper',
                    filterName : 'hue'
                },
                {
                    step       : 1,
                    min        : 0,
                    max        : 100,
                    icon       : 'regular/lemon',
                    filterName : 'sepia'
                },
                {
                    step       : 0.1,
                    min        : 0,
                    max        : 10,
                    icon       : 'flask',
                    filterName : 'gamma'
                },
                {
                    step       : 1,
                    min        : 0,
                    max        : 100,
                    icon       : 'regular/dot-circle',
                    filterName : 'noise'
                },
                {
                    step       : 1,
                    min        : 0,
                    max        : 100,
                    icon       : 'cut',
                    filterName : 'clip'
                },
                {
                    step       : 1,
                    min        : 0,
                    max        : 100,
                    icon       : 'regular/gem',
                    filterName : 'sharpen'
                },
                {
                    step       : 1,
                    min        : 0,
                    max        : 20,
                    icon       : 'glass-martini',
                    filterName : 'stackBlur'
                },
                {
                    step       : null,
                    min        : null,
                    max        : null,
                    icon       : 'shield-alt',
                    filterName : 'greyscale'
                },
                {
                    step       : null,
                    min        : null,
                    max        : null,
                    icon       : 'cube',
                    filterName : 'invert'
                }
            ],
            noController: [
                'greyscale',
                'invert'
            ]
        }
    }
}
</script>
