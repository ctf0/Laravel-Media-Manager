<template>
    <div>
        <controls v-for="item in filtersList"
                  :key="item.filterName"
                  :min="item.min"
                  :max="item.max"
                  :step="item.step"
                  :icon="item.icon"
                  :filter-name="item.filterName"
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
        'reset',
        'applyFilter',
        'camanFilters'
    ],
    data() {
        return {
            filtersList: [
                {
                    step: 1,
                    min: -100,
                    max: 100,
                    icon: 'sun-o',
                    filterName: 'brightness'
                },
                {
                    step: 1,
                    min: -100,
                    max: 100,
                    icon: 'adjust',
                    filterName: 'contrast'
                },
                {
                    step: 1,
                    min: -100,
                    max: 100,
                    icon: 'low-vision',
                    filterName: 'saturation'
                },
                {
                    step: 1,
                    min: -100,
                    max: 100,
                    icon: 'flash',
                    filterName: 'vibrance'
                },
                {
                    step: 1,
                    min: -100,
                    max: 100,
                    icon: 'thermometer-half',
                    filterName: 'exposure'
                },
                {
                    step: 1,
                    min: 0,
                    max: 100,
                    icon: 'eyedropper',
                    filterName: 'hue'
                },
                {
                    step: 1,
                    min: 0,
                    max: 100,
                    icon: 'lemon-o',
                    filterName: 'sepia'
                },
                {
                    step: 0.1,
                    min: 0,
                    max: 10,
                    icon: 'flask',
                    filterName: 'gamma'
                },
                {
                    step: 1,
                    min: 0,
                    max: 100,
                    icon: 'dot-circle-o',
                    filterName: 'noise'
                },
                {
                    step: 1,
                    min: 0,
                    max: 100,
                    icon: 'scissors',
                    filterName: 'clip'
                },
                {
                    step: 1,
                    min: 0,
                    max: 100,
                    icon: 'diamond',
                    filterName: 'sharpen'
                },
                {
                    step: 1,
                    min: 0,
                    max: 20,
                    icon: 'glass',
                    filterName: 'stackBlur'
                },
                {
                    step: null,
                    min: null,
                    max: null,
                    icon: 'shield',
                    filterName: 'greyscale'
                },
                {
                    step: null,
                    min: null,
                    max: null,
                    icon: 'cube',
                    filterName: 'invert'
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
