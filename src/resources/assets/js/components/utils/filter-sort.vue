<template>
    <div class="dropdown"
         :class="{'is-active' : show}"
         @click="togglePanel()">
        <div class="dropdown-trigger">
            <button class="button"
                    :disabled="disabled"
                    aria-haspopup="true"
                    aria-controls="dropdown-menu">
                <span>{{ trans('filtration') }}</span>
                <span v-show="!disabled"
                      class="icon is-small">
                    <icon name="angle-down"/>
                </span>
            </button>
        </div>

        <div id="dropdown-menu"
             class="dropdown-menu"
             role="menu">
            <div class="dropdown-content">
                <!-- filters -->
                <div v-for="item in filters"
                     :key="`filter-${item}`"
                     class="dropdown-item"
                     :class="[
                         filterNameIs(item) ? 'has-text-weight-bold has-text-link' : 'has-text-grey-dark',
                         haveFileType(item) ? 'link' : 'has-text-grey-light disabled'
                     ]"
                     @click="setFilterName(item)">
                    {{ trans(item) }}
                </div>

                <!-- sorts -->
                <hr class="dropdown-divider">
                <div v-for="item in sorts"
                     :key="`sort-${item}`"
                     class="dropdown-item link"
                     :class="sortNameIs(item) ? 'has-text-weight-bold has-text-link' : 'has-text-grey-dark'"
                     @click="setSortName(item)">
                    {{ trans(item) }}
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    .dropdown-item {
        padding-top: 0;
    }

    .disabled {
        cursor: not-allowed;
    }

</style>

<script>
export default {
    props: [
        'setFilterName',
        'filterNameIs',
        'setSortName',
        'sortNameIs',
        'disabled',
        'haveAFileOfType',
        'translations'
    ],
    data() {
        return {
            show: false,
            filters: [
                'image',
                'video',
                'audio',
                'folder',
                'text',
                'application',
                'locked',
                'selected',
                'non'
            ],
            sorts: [
                'name',
                'size',
                'last_modified',
                'non'
            ]
        }
    },
    mounted() {
        document.addEventListener('click', this.hidePanel)
    },
    destroy() {
        document.addEventListener('click', this.hidePanel)
    },
    methods: {
        hidePanel(e) {
            if (!this.$el.contains(e.target)) {
                this.show = false
            }
        },
        togglePanel() {
            this.show = this.disabled
                ? false
                : !this.show
        },
        trans(val) {
            return this.translations[val]
        },
        haveFileType(val) {
            if (val == 'non') {
                return true
            }

            return this.haveAFileOfType(val)
        }
    }
}
</script>
