<template>
  <div class="header">
    <div class="logo">
      <img :src="image" />
    </div>

    <div
      class="back-button"
      :class="animationClass"
      style="animation-duration: .7s"
      v-if="showBack"
      @mouseover="mouseOver"
      @mouseleave="mouseLeave"
      @click="backButtonPressed"
    >
      <img v-if="hover" src="/images/back-hover.svg" />
      <img v-else src="/images/back.svg" />
      <span>Back</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'header-component',
  props: ['showBack', 'image'],
  data() {
    return {
      animationClass: '',
      hover: false,
    };
  },
  methods: {
    mouseOver() {
      this.animationClass = 'pulse';
      this.hover = true;
    },
    mouseLeave() {
      this.animationClass = '';
      this.hover = false;
    },
    backButtonPressed() {
      this.$emit('back-button-pressed');
    },
  },
};
</script>
