<template>
    <div class="dropdown"
         :class="{'is-active' : show}"
         @click.stop="togglePanel()">
        <div class="dropdown-trigger">
            <button class="button"
                    :disabled="disabled"
                    aria-haspopup="true"
                    aria-controls="dropdown-menu">
                <span>
                    <slot name="title"/>
                </span>
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
                <slot name="content"/>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    .dropdown-menu {
        left: unset;
        right: 0;
    }

    .dropdown-item {
        padding-top: 0;
    }

</style>

<script>
export default {
    props: ['disabled'],
    data() {
        return {
            show: false
        }
    },
    created() {
        document.addEventListener('click', this.hidePanel)
    },
    beforeDestroy() {
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
        }
    },
    watch: {
        show(val) {
            if (!val) {
                document.addEventListener('click', this.hidePanel)
            }
        }
    }
}
</script>
