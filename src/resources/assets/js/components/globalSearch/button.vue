<template>
    <button :disabled="loading" :class="{'is-static' : loading}" class="button" @click="done ? showSearchPanel() : init()">
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
                    parent.showNotif(parent.trans('glbl_search'))

                }).catch((err) => {
                    console.error(err)
                })
        },
        showSearchPanel() {
            EventHub.fire('show-global-search')
        }
    }
}
</script>
