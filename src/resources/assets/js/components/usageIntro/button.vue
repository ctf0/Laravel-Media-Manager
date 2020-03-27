<template>
    <div class="usage-intro-btn"
         @click.stop="showPanel()">
        <span class="icon is-large">
            <icon name="pref"
                  scale="1.2"/>
        </span>
    </div>
</template>

<script>
export default {
    name: 'usage-intro-btn',
    methods: {
        showPanel() {
            EventHub.fire('show-usage-intro')
        }
    }
}
</script>
