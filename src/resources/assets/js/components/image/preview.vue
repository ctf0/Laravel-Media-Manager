<template>
    <div>
        <div data-img-container
             @scroll="updateScrollDir">
            <slot/>
        </div>

        <transition :name="scrollBtn.state ? 'mm-img-nxt' : 'mm-img-prv'"
                    appear>
            <div v-show="scrollBtn.state"
                 class="scroll-btn"
                 :class="scrollBtn.dir"
                 @click.self.stop="scrollImg">
                <span class="icon is-large">
                    <icon name="chevron-down"
                          scale="1"/>
                </span>
            </div>
        </transition>
    </div>
</template>

<script>
import debounce from 'lodash/debounce'
import animateScrollTo from '../../packages/animated-scroll-to'

export default {
    data() {
        return {
            scrollBtn: {
                state: false,
                dir: 'down'
            }
        }
    },
    created() {
        window.addEventListener('resize', this.isScrollable)
    },
    mounted() {
        this.isScrollable()
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.isScrollable)
    },
    methods: {
        isScrollable() {
            let item = this.getContainer(this.$el)

            if (item) {
                return this.scrollBtn.state = item.scrollHeight > item.offsetHeight
            }
        },
        updateScrollDir: debounce(function (e) {
            let item = e.target
            let margin = 3

            return this.scrollBtn.dir = (item.scrollTop + item.clientHeight) >= (item.scrollHeight - margin)
                ? 'up'
                : 'down'
        }, 250),
        scrollImg(e) {
            let item = this.getContainer(e.target.parentNode)

            return animateScrollTo(item, {
                speed: 250,
                maxDuration: 500,
                offset: this.scrollBtn.dir == 'up' ? -item.scrollHeight : item.scrollHeight,
                element: item,
                useKeys: true
            })
        },
        getContainer(el) {
            return el.querySelector('[data-img-container]')
        }
    }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/partials/vars';

.wrapper {
    overflow: hidden;
    position: relative;

    > div {
        max-height: 40vh;
        overflow-y: scroll;
    }

    .scroll-btn {
        background: $image;
        border-radius: 100vw;
        bottom: 1rem;
        color: $white;
        position: absolute;
        right: 1rem;
        transition: all $anim-time;

        span,
        span * {
            pointer-events: none;
        }

        &.up {
            transform: rotate(180deg);

            &:hover {
                box-shadow: 0 -15px 30px 0 rgba($black, 0.11), 0 -5px 15px 0 rgba($black, 0.08);
                transform: translateY(-0.7rem) rotate(180deg);
            }

            &:active {
                box-shadow: 0 -10px 10px 0 rgba($black, 0.15), 0 -5px 10px 0 rgba($black, 0.1);
                transform: translateY(-0.7rem) scale(0.8) rotate(180deg);
            }
        }

        &:hover {
            box-shadow: 0 15px 30px 0 rgba($black, 0.11), 0 5px 15px 0 rgba($black, 0.08);
            transform: translateY(-0.7rem);
        }

        &:active {
            box-shadow: 0 10px 10px 0 rgba($black, 0.15), 0 5px 10px 0 rgba($black, 0.1);
            transform: translateY(-0.7rem) scale(0.8);
        }
    }
}

</style>
