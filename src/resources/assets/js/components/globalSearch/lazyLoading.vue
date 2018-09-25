<template>
    <div>
        <img v-if="src" ref="img" :src="src" :alt="file.name" async>
        <!-- <img v-else src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"> -->
        <img v-else src="/assets/vendor/MediaManager/patterns/gif/5b98026bab87a.gif">
    </div>
</template>

<style scoped>
    img {
        background: black;
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
                    this.fetchImg().then((img) => {
                        this.src = img
                    })
                }
            }
        }
    }
}
</script>
