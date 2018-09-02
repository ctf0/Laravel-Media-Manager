<template>
    <div class="progress">
        <p v-tippy="{theme: 'light', arrow: true, followCursor: true}"
           v-for="(val, key) in contentRatio"
           :class="`is-${key}`"
           :style="getRatio(val)"
           :title="getToolTipContent(key, val)"
           :key="key"
           class="progress-bar progress-bar-striped active">
            <strong v-show="val > 0">{{ key }}</strong>
        </p>
    </div>
</template>

<style scoped lang="scss">
    .progress-bar {
        box-shadow: none;
        background-color: black;
        position: relative;

        &::after {
            position: absolute;
            top: 0;
            width: 100%;
            height: 100%;
            content: '';
            mix-blend-mode: screen;
        }

        strong {
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: uppercase;
            mix-blend-mode: difference;
        }
    }
</style>

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
            handler(val, oldVal) {
                this.$nextTick(() => {
                    let ratio = {
                        image: 0,
                        audio: 0,
                        video: 0,
                        text: 0,
                        folder: 0,
                        application: 0,
                        pdf: 0
                    }

                    val.forEach((e) => {
                        if (this.fileTypeIs(e, 'audio')) ratio.audio++
                        if (this.fileTypeIs(e, 'video')) ratio.video++
                        if (this.fileTypeIs(e, 'image')) ratio.image++
                        if (this.fileTypeIs(e, 'text')) ratio.text++
                        if (this.fileTypeIs(e, 'pdf')) ratio.pdf++
                        if (this.fileTypeIs(e, 'folder')) ratio.folder++
                        if (this.fileTypeIs(e, 'application')) ratio.application++
                    })

                    return this.contentRatio = ratio
                })
            }
        }
    }
}
</script>
