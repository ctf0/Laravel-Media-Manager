<template>
    <div :class="{'active': show}"
         class="gesture-overlay"/>
</template>

<style scoped lang="scss">
    @import '../../../sass/partials/vars';

    .gesture-overlay {
        background-color: rgba($blue, 0.25);
        bottom: 0;
        left: 0;
        opacity: 0;
        position: absolute;
        right: 0;
        top: 0;
        transition: all $anim-time;
        visibility: hidden;
        z-index: 10;
    }

    .active {
        opacity: 1;
        visibility: visible;
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
