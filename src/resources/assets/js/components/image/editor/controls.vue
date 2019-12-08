<template>
    <div>
        <button v-for="item in controlsList"
                :key="item.op"
                v-tippy="{arrow: true, theme: 'mm'}"
                :class="{'is-active': item.mode ? dragModeIs(item.mode) : false}"
                :title="trans(item.trans)"
                :disabled="processing"
                class="btn-plain"
                @click.stop="operations(item.op)">
            <span class="icon"><icon :name="processing ? 'spinner' : item.icon"
                                     :pulse="processing"/></span>
        </button>
    </div>
</template>

<script>
export default {
    props: [
        'dragModeIs',
        'trans',
        'operations',
        'processing'
    ],
    data() {
        return {
            controlsList: [
                {
                    trans: 'move',
                    op: 'move',
                    mode: 'move',
                    icon: 'arrows'
                },
                {
                    trans: 'crop',
                    op: 'crop',
                    mode: 'crop',
                    icon: 'crop'
                },
                {
                    trans: 'crop_zoom_in',
                    op: 'zoom-in',
                    mode: null,
                    icon: 'search-plus'
                },
                {
                    trans: 'crop_zoom_out',
                    op: 'zoom-out',
                    mode: null,
                    icon: 'search-minus'
                },
                {
                    trans: 'crop_rotate_left',
                    op: 'rotate-left',
                    mode: null,
                    icon: 'rotate-left'
                },
                {
                    trans: 'crop_rotate_right',
                    op: 'rotate-right',
                    mode: null,
                    icon: 'rotate-right'
                },
                {
                    trans: 'crop_flip_horizontal',
                    op: 'flip-horizontal',
                    mode: null,
                    icon: 'arrows-h'
                },
                {
                    trans: 'crop_flip_vertical',
                    op: 'flip-vertical',
                    mode: null,
                    icon: 'arrows-v'
                }
            ]
        }
    }
}
</script>
