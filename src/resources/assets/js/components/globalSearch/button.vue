<template>
    <button v-tippy :disabled="loading"
            :class="{'is-static' : loading}"
            :title="trans('glbl_search')"
            class="button"
            @click="done ? showSearchPanel() : init()">
        <span class="icon"><icon :spin="loading" name="globe"/></span>
    </button>
</template>

<script>
export default {
    props: ['route'],
    data() {
        return {
            loading: false,
            done: false
        }
    },
    mounted() {
        EventHub.listen('clear-global-search', () => {
            this.done = false
        })
    },
    methods: {
        init() {
            const parent = this.$parent
            this.loading = true

            axios.get(this.route)
                .then(({data}) => {
                    this.loading = false
                    this.done = true
                    EventHub.fire('global-search-index', data)
                    parent.showNotif(this.trans('glbl_search_avail'))

                }).catch((err) => {
                    console.error(err)
                })
        },
        trans(key) {
            return this.$parent.trans(key)
        },
        showSearchPanel() {
            EventHub.fire('show-global-search')
        }
    }
}
</script>
