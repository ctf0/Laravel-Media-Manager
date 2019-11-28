<template>
    <div>
        <img v-if="src"
             ref="img"
             :src="src"
             :alt="file.name"
             loading="lazy">
        <img v-else
             src="/assets/vendor/MediaManager/patterns/gif/5b98026bab87a.gif">
    </div>
</template>

<style scoped>
    img {
        background-color: black;
    }

</style>

<script>
import lazy from '../../mixins/lazy'

export default {
    mixins: [lazy],
    watch: {
        intersected: {
            immediate: true,
            handler(val, oldVal) {
                if (val) {
                    this.src = this.file.path
                }
            }
        }
    }
}
</script>
