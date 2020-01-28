<template>
  <div
    class="body"
    :class="overflowClass + ' ' + ((additionalMessage) ? 'has-additional-message' : '')"
  >
    <div class="table">
      <div class="row first" :style="overflowFirstRowStyle">
        <div class="cell">
          <div class="message-wrapper" :style="overflowMessageWrapperStyle">
            <transition
              enter-active-class="fadeInUp"
              leave-active-class="fadeOutUp"
            >
              <div
                v-if="showMessage"
                ref="message"
                style="animation-duration: 1s"
                :style="overflowMessageStyle"
              >
                <div class="title">{{ title }}</div>
                <div class="message">{{ message }}</div>
              </div>
            </transition>
          </div>
        </div>
      </div>
      <div class="row last">
        <div class="cell">
          <div class="button-wrapper" :class="(currentIndex > 0) ? 'align-center' : ''">
            <div>
              <template v-for="i in (totalMessages - 1)">
                <transition
                  enter-active-class="fadeInUp"
                  leave-active-class="fadeOut"
                >
                  <div
                    v-if="currentIndex > 0"
                    :ref="'progressIndicator' + i"
                    style="animation-duration: 0.5s"
                    :style="'animation-delay: ' + (0.2 * (i - 1)) + 's'"
                    class="progress-indicator" :class="progressIndicatorClass(i)"
                  ></div>
                </transition>
              </template>
            </div>

            <transition
              enter-active-class="fadeInUp"
              leave-active-class="fadeOut"
            >
              <div
                v-if="showButton"
                class="button"
                ref="button"
                :class="buttonAnimationClass"
                @click="buttonClick"
                style="animation-duration: 1s"
              >
                <div class="top"></div>
                <div class="right"></div>
                <div class="bottom"></div>
                <div class="left"></div>
                <div class="background"></div>

                <span>{{ button.text }}</span>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <div v-if="additionalMessage" class="row">
        <transition
          enter-active-class="fadeIn"
          leave-active-class="fadeOut"
        >
          <div
            v-if="showAdditionalMessage"
            class="additional-message"
            style="animation-duration: 1s"
            v-html="additionalMessage"
          >
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'body-component',
  props: ['title', 'message', 'additionalMessage', 'button', 'currentIndex', 'totalMessages'],
  data() {
    return {
      buttonAnimationClass: '',
      overflowClass: '',
      overflowFirstRowStyle: '',
      overflowMessageStyle: '',
      overflowMessageWrapperStyle: '',
      progressIndicatorClass1: '',
      progressIndicatorClass2: '',
      progressIndicatorClass3: '',
      progressIndicatorClass4: '',
      progressIndicatorClass5: '',
      showAdditionalMessage: true,
      showButton: true,
      showMessage: true,
    };
  },
  watch: {
    message() {
      this.checkOverflow();
    },
    currentIndex() {
      this.progressIndicatorClass1 = (this.currentIndex > 1) ? 'active' : '';
      this.progressIndicatorClass2 = (this.currentIndex > 2) ? 'active' : '';
      this.progressIndicatorClass3 = (this.currentIndex > 3) ? 'active' : '';
      this.progressIndicatorClass4 = (this.currentIndex > 4) ? 'active' : '';
      this.progressIndicatorClass5 = (this.currentIndex > 5) ? 'active' : '';
    },
  },
  mounted() {
    window.addEventListener('resize', this.checkOverflow);
  },
  methods: {
    progressIndicatorClass(i) {
      return this['progressIndicatorClass' + i];
    },
    checkOverflow() {
      this.overflowClass = '';
      this.overflowFirstRowStyle = '';
      this.overflowMessageStyle = '';
      this.overflowMessageWrapperStyle = '';

      this.$nextTick(() => {
        if (document.querySelector('.page').clientHeight > window.innerHeight) {
          this.overflowClass = 'overflow';

          if (window.innerWidth <= 375) {
            this.overflowFirstRowStyle = 'height: ' + (window.innerHeight - 180) + 'px';
            this.overflowMessageWrapperStyle = 'height: ' + (window.innerHeight - 180) + 'px';
            this.overflowMessageStyle = 'height: ' + (window.innerHeight - 200) + 'px';
          } else {
            this.overflowFirstRowStyle = 'height: ' + (window.innerHeight - 230) + 'px';
            this.overflowMessageWrapperStyle = 'height: ' + (window.innerHeight - 230) + 'px';
            this.overflowMessageStyle = 'height: ' + (window.innerHeight - 270) + 'px';
          }
        } else {
          this.overflowClass = '';
        }
      });
    },
    buttonClick() {
      if (this.currentIndex == 0) {
        this.buttonAnimationClass = 'spread';

        setTimeout(() => {
          this.overflowClass = '';
          this.overflowStyle = '';
          this.showButton = false;
          this.showMessage = false;
          this.showAdditionalMessage = false;

          setTimeout(() => {
            this.$emit('button-click', this.button.action);

            this.showAdditionalMessage = true;
            this.showMessage = true;

            setTimeout(() => {
              this.showButton = true;
            }, 800);
          }, 1500);
        }, 300);
      } else {
        const button = this.$refs.button;

        this.buttonAnimationClass = 'spread';

        setTimeout(() => {
          button.style.width = button.clientWidth + 'px';
          button.style.height = button.clientHeight + 'px';

          this.buttonAnimationClass = 'no-padding';

          setTimeout(() => {
            button.style.width = '18px';
            this.buttonAnimationClass = 'no-padding reduce-size';

            setTimeout(() => {
              const progressIndicator = this.$refs['progressIndicator' + this.currentIndex][0];
              const progressIndicatorLeft = progressIndicator.getBoundingClientRect().left;
              const buttonLeft = button.getBoundingClientRect().left;
              const buttonExpandedWidth = (buttonLeft - progressIndicatorLeft + 9) + 'px';

              button.style.width = buttonExpandedWidth;
              this.buttonAnimationClass = 'no-padding reduce-size expand-width';

              setTimeout(() => {
                this.buttonAnimationClass = 'no-padding reduce-size expand-width hidden';
                progressIndicator.style.width = buttonExpandedWidth;

                this['progressIndicatorClass' + this.currentIndex] = 'active';

                setTimeout(() => {
                  this['progressIndicatorClass' + this.currentIndex] = 'active reduce-size';

                  setTimeout(() => {
                    if (this.button.action == 'continue') {
                      progressIndicator.style.width = '';
                      button.style.width = '';
                      button.style.height = '';

                      this.buttonAnimationClass = '';
                      this['progressIndicatorClass' + this.currentIndex] = 'active';
                    }

                    this.$emit('button-click', this.button.action);

                    this.showAdditionalMessage = true;
                    this.showMessage = true;

                    setTimeout(() => {
                      this.showButton = true;
                    }, 800);
                  }, 200);
                }, 10);
              }, 200);
            }, 400);
          }, 10);
        }, 300);
      }
    },
  },
  beforeDestroy: function () {
    window.removeEventListener('resize', this.checkOverflow);
  },
};
</script>
