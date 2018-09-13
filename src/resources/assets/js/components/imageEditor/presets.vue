<template>
    <div v-if="imageCropper" class="__caman-presets">
        <h3>Presets</h3>

        <section>
            <div class="col">
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('clarity')}"
                        class="btn-plain"
                        title="Clarity"
                        @click="applyFilter('clarity')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Clarity') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('pinhole')}"
                        class="btn-plain"
                        title="Pinhole"
                        @click="applyFilter('pinhole')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Pinhole') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('love')}"
                        class="btn-plain"
                        title="Love"
                        @click="applyFilter('love')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Love') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('jarques')}"
                        class="btn-plain"
                        title="Jarques"
                        @click="applyFilter('jarques')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Jarques') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('orangePeel')}"
                        class="btn-plain"
                        title="Orange Peel"
                        @click="applyFilter('orangePeel')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Orange Peel') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('sinCity')}"
                        class="btn-plain"
                        title="Sin City"
                        @click="applyFilter('sinCity')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Sin City') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('grungy')}"
                        class="btn-plain"
                        title="Grungy"
                        @click="applyFilter('grungy')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Grungy') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('oldBoot')}"
                        class="btn-plain"
                        title="Old Boot"
                        @click="applyFilter('oldBoot')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Old Boot') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('lomo')}"
                        class="btn-plain"
                        title="Lomo"
                        @click="applyFilter('lomo')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Lomo') }}</span>
                </button>
            </div>
            <div class="col">
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('vintage')}"
                        class="btn-plain"
                        title="Vintage"
                        @click="applyFilter('vintage')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Vintage') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('crossProcess')}"
                        class="btn-plain"
                        title="Cross Process"
                        @click="applyFilter('crossProcess')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Cross Process') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('concentrate')}"
                        class="btn-plain"
                        title="Concentrate"
                        @click="applyFilter('concentrate')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Concentrate') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('glowingSun')}"
                        class="btn-plain"
                        title="Glowing Sun"
                        @click="applyFilter('glowingSun')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Glowing Sun') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('sunrise')}"
                        class="btn-plain"
                        title="Sunrise"
                        @click="applyFilter('sunrise')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Sunrise') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('nostalgia')}"
                        class="btn-plain"
                        title="Nostalgia"
                        @click="applyFilter('nostalgia')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Nostalgia') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('hemingway')}"
                        class="btn-plain"
                        title="Hemingway"
                        @click="applyFilter('hemingway')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Hemingway') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('herMajesty')}"
                        class="btn-plain"
                        title="Her Majesty"
                        @click="applyFilter('herMajesty')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Her Majesty') }}</span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :class="{'is-active': isUsed('hazyDays')}"
                        class="btn-plain"
                        title="Hazy Days"
                        @click="applyFilter('hazyDays')">
                    <span v-show="processing" class="icon is-small"><icon :pulse="processing" name="spinner"/></span>
                    <span v-show="!processing">{{ truncate('Hazy Days') }}</span>
                </button>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    props: ['imageCropper', 'processing'],
    methods: {
        truncate(str) {
            return str.match(/\b(\w)/g).join('')
        },
        isUsed(name) {
            return this.$parent.camanFilters.hasOwnProperty(name)
        },
        applyFilter(name) {
            return this.$parent.applyFilter(name, null)
        }
    }
}
</script>
