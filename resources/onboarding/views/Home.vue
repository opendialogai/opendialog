<template>
  <div class="page" :style="'height: ' + windowHeight + '; background: ' + backgroundColor">
    <Header
      :image="headerImage"
      :show-back="showBack"
      @back-button-pressed="backButtonPressed"
    />
    <Body
      :title="title"
      :message="message"
      :additionalMessage="additionalMessage"
      :button="button"
      :currentIndex="currentIndex"
      :totalMessages="totalMessages"
      @button-click="buttonClick"
    />
  </div>
</template>

<script>
import Body from '../components/Body';
import Header from '../components/Header';

export default {
  name: 'home',
  props: ['id'],
  components: {
    Body,
    Header,
  },
  data() {
    return {
      backgroundColor: '#1b212a',
      currentIndex: 0,
      forceRecompute: 0,
      headerImage: '/images/onboarding-logo.svg',
      messages: [
        {
          title: 'Welcome to Opendialog Bot.',
          message: 'Test Message.',
          additionalMessage: 'Test Additional Message.',
          button: {
            action: 'link_to_chat',
            text: 'Yes, let\'s get started!',
          },
        },
      ],
      showBack: false,
    };
  },
  computed: {
    title() {
      return this.messages[this.currentIndex].title;
    },
    message() {
      return this.messages[this.currentIndex].message;
    },
    additionalMessage() {
      return this.messages[this.currentIndex].additionalMessage;
    },
    button() {
      return this.messages[this.currentIndex].button;
    },
    windowHeight() {
      const recompute = this.forceRecompute;
      return window.innerHeight + 'px';
    },
    totalMessages() {
      return this.messages.length;
    },
  },
  watch: {
    '$route' () {
      this.updateCurrentIndex();
    },
  },
  mounted() {
    this.updateCurrentIndex();

    window.addEventListener('resize', () => this.forceRecompute++ );
  },
  methods: {
    updateCurrentIndex() {
      this.currentIndex = (this.id) ? parseInt(this.id, 10) : 0;
      this.showBack = (this.currentIndex > 0) ? true : false;
    },
    backButtonPressed() {
      if (this.currentIndex == 1) {
        this.$router.push({ name: 'home' });
      } else {
        this.$router.push({ name: 'landing', params: { id: (this.currentIndex - 1) } });
      }
    },
    buttonClick(action) {
      if (action == 'continue') {
        if ((this.currentIndex + 1) < this.messages.length) {
          this.$router.push({ name: 'landing', params: { id: (this.currentIndex + 1) } });
        }
      } else if (action == 'link_to_chat') {
        window.location.href = '/web-chat';
      }
    },
  },
};
</script>
