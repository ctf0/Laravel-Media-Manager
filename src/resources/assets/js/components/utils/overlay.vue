<template>
    <div :class="{'active': show}" class="gesture-overlay"/>
</template>

<style scoped lang="scss">
    @import '../../../sass/partials/vars';

    .gesture-overlay {
        position: absolute;
        z-index: 10;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        visibility: hidden;
        opacity: 0;
        transition: all 0.2s;
        background-color: rgba($blue, 0.25);
    }

    .active {
        visibility: visible;
        opacity: 1;
    }
</style>

<script>
export default {
    data() {
        return {
            show: false
        }
    },
    created() {
        EventHub.listen('toggle-overlay', () => {
            this.show = true

            setTimeout(() => {
                this.show = false
            }, 200)
        })
    }
}
</script>
