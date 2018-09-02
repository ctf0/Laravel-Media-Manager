<template>
    <div>
        <img v-if="src" ref="img" :src="src" :alt="file.name" async>
    </div>
</template>

<script>
import lazy from '../../mixins/lazy'

export default {
    mixins: [lazy]
}
</script>
