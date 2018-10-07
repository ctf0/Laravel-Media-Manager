<template>
    <div class="progress">
        <p v-tippy="{theme: 'light', arrow: true, followCursor: true}"
           v-for="(val, key) in contentRatio"
           :class="`is-${key}`"
           :style="getRatio(val)"
           :title="getToolTipContent(key, val)"
           :key="key"
           class="progress-bar animate">
            <strong v-show="val > 0">{{ key }}</strong>
        </p>
    </div>
</template>

<style scoped lang="scss" src="../../../sass/modules/ratio-bar.scss"></style>

<script>
export default {
    props: ['list', 'total', 'fileTypeIs'],
    data() {
        return {
            contentRatio: {}
        }
    },
    methods: {
        getRatio(val) {
            return {
                width: this.calcRatio(val)
            }
        },
        calcRatio(val) {
            return (val / this.total * 100).toFixed(2) + '%'
        },
        getToolTipContent(k, v) {
            return `<p class="title is-marginless">${v}</p><p class="heading">${k}</p>`
        }
    },
    watch: {
        list: {
            immediate: true,
            deep: true,
            handler(val, oldVal) {
                setTimeout(() => {
                    let ratio = {
                        image: 0,
                        audio: 0,
                        video: 0,
                        text: 0,
                        folder: 0,
                        application: 0
                    }

                    val.forEach((e) => {
                        if (this.fileTypeIs(e, 'audio')) ratio.audio++
                        if (this.fileTypeIs(e, 'video')) ratio.video++
                        if (this.fileTypeIs(e, 'image')) ratio.image++
                        if (this.fileTypeIs(e, 'folder')) ratio.folder++
                        if (this.fileTypeIs(e, 'text') || this.fileTypeIs(e, 'pdf')) ratio.text++
                        if (this.fileTypeIs(e, 'application') || this.fileTypeIs(e, 'compressed')) ratio.application++
                    })

                    this.contentRatio = ratio
                }, 100)
            }
        }
    }
}
</script>
