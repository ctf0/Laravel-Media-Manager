<template>
    <div>
        <!-- single -->
        <transition name="slide-fade" v-if="self_show">
            <div :class="classObj(self_type)" class="item">

                <button class="delete" @click="self_show = false"></button>
                <div class="media">
                    <div class="media-left" v-if="self_icon">
                        <figure class="icon is-large">
                            <i class="material-icons">{{ getIcon(self_type) }}</i>
                        </figure>
                    </div>
                    <div class="media-content">
                        <h4 class="title">
                            <strong>{{ self_title }}</strong>
                        </h4>
                        <p class="subtitle">{{ self_body }}</p>
                    </div>
                </div>

            </div>
        </transition>

        <!-- events -->
        <template v-if="!self_title">
            <span id="close_all" class="tag is-dark is-medium"
                v-if="checkForGroup()" @click="closeAll()">
                Close All
                <button class="delete"></button>
            </span>

            <transition-group name="slide-fade" tag="ul">
                <li v-for="(one,index) in notif_group" :key="index"
                    class="item" :class="classObj(one.type)"
                    v-if="IsVisible(index)">

                    <button class="delete" @click="closeNotif(index)"></button>
                    <div class="media">
                        <div class="media-left" v-if="one.icon">
                            <figure class="icon is-large">
                                <i class="material-icons">{{ getIcon(one.type) }}</i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <h4 class="title">
                                <strong>{{ one.title }}</strong>
                            </h4>
                            <p class="subtitle">{{ one.body }}</p>
                        </div>
                    </div>

                </li>
            </transition-group>
        </template>
    </div>
</template>

<style scoped>
    @import url(https://fonts.googleapis.com/icon?family=Material+Icons);

    /*animation*/
    .slide-fade-enter-active,
    .slide-fade-leave-active {
        transition: all 0.3s ease;
    }
    .slide-fade-enter,
    .slide-fade-leave-to {
        opacity: 0;
        transform: translateX(10px);
    }

    /*notiifcation card*/
    .item {
        width: 330px;
    }
    .material-icons {
        font-size: 3rem;
    }
    .media-left {
        align-self: center;
        position: relative;
        margin-right: 1.25rem;
    }

    .has-shadow {
        box-shadow: 0 2px 4px rgba(0,0,0,0.12), 0 0 6px rgba(0,0,0,0.04);
    }
    .notification {
        padding: 1.25rem;
        margin-bottom: 10px;
    }

    #close_all {
        background-color: rgba(54, 54, 54, 0.9);
        cursor: pointer;
        position: fixed;
        z-index: 1;
        top: 1rem;
        right: 1rem;
    }
    #close_all:hover{
        background-color: rgb(54, 54, 54);
    }
</style>

<script>
export default {
    props: {
        title: '',
        body: '',
        icon: {
            default: true
        },
        type: {default: 'info'},
        duration: null
    },

    data() {
        return {
            notif_group: [],
            self_title: this.title,
            self_body: this.body,
            self_type: this.type,
            self_icon: Boolean(this.icon),
            self_duration: this.duration,
            self_show: false
        }
    },

    created() {
        this.checkProp()

        EventHub.listen('showNotif', (data) => {
            this.collectData(data)
        })
    },

    methods: {
        checkForGroup() {
            return this.notif_group.length > 1 &&
                    this.notif_group.filter((item) => item.show == true).length > 1
        },
        closeAll() {
            this.notif_group.map((item) => {
                item.show = false
                item.duration = null
            })
        },
        checkProp() {
            if (this.self_title) {
                this.self_show = true
            }

            if (this.self_duration !== undefined) {
                setTimeout(() => {
                    this.self_show = false
                }, this.self_duration * 1000)
            }
        },
        collectData(data) {
            this.notif_group.push({
                title: data.title,
                body: data.body,
                type: data.type,
                icon: data.icon == null ? true : false,
                duration: data.duration,
                onClose: data.onClose,
                show: true
            })
        },
        IsVisible(index) {
            let dur = this.notif_group[index].duration

            if (dur !== undefined) {
                setTimeout(() => {
                    this.closeNotif(index)
                }, dur * 1000)
            }

            return this.notif_group[index].show
        },
        closeNotif(index) {
            this.notif_group[index].show = false

            if (typeof this.notif_group[index].onClose != 'undefined' && typeof this.notif_group[index].onClose === 'function') {
                this.notif_group[index].onClose()
            }
        },
        classObj(type) {
            return `notification has-shadow is-${type}`
        },
        getIcon(type) {
            switch (type) {
            case 'primary':
                return 'track_changes'
            case 'success':
                return 'check_circle'
            case 'info':
                return 'live_help'
            case 'warning':
                return 'power_settings_new'
            case 'danger':
                return 'add_alert'
            default:
                return 'error'
            }
        }
    }
}
</script>
