<template>
    <button v-tippy="{arrow: true}"
            :disabled="loading || isLoading"
            :title="trans('glbl_search')"
            class="button"
            @click.stop="done ? showSearchPanel() : init()">
        <span class="icon">
            <icon :spin="loading"
                  name="globe-americas"/>
        </span>
    </button>
</template>

<script>
export default {
    props: ['route', 'isLoading', 'trans', 'showNotif'],
    data() {
        return {
            loading : false,
            done    : false
        }
    },
    mounted() {
        EventHub.listen('clear-global-search', () => {
            this.done = false
        })
    },
    methods: {
        init() {
            this.loading = true

            axios.get(this.route)
                .then(({data}) => {
                    this.loading = false
                    this.done    = true
                    EventHub.fire('global-search-index', data)
                    this.showNotif(this.trans('glbl_search_avail'))

                }).catch((err) => {
                    console.error(err)
                })
        },
        showSearchPanel() {
            EventHub.fire('toggle-global-search', true)
        }
    }
}
</script>
