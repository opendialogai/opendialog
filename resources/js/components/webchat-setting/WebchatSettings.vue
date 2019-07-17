<template>
  <div>
    <h2>Webchat settings</h2>
    <h3 v-if="subCategory" class="font-weight-bolder">{{ subCategory }}</h3>

    <div class="settings mt-4">
      <template v-for="setting in webchatSettings">
        <b-card no-body>
          <b-card-body class="pt-2 pb-2 pl-4 pr-4 row">
            <div class="card-title mt-2 mb-0 col-6">{{ setting.name }}</div>
            <div class="card-text col-6 row">
              <template v-if="setting.type == 'boolean'">
                <b-switch variant="pill" color="dark" v-model="setting.value" @change="saveSetting(setting)" />
              </template>
              <template v-else-if="setting.type == 'string'">
                <input class="form-control" v-model="setting.value" @blur="saveSetting(setting)" />
              </template>
              <template v-else-if="setting.type == 'number'">
                <input class="form-control" type="number" v-model="setting.value" @blur="saveSetting(setting)" />
              </template>
              <template v-else-if="setting.type == 'colour'">
                <input type="text" class="form-control color" readonly :value="setting.value" @focus="displayPicker = setting.id" />
                <span class="color-picker-container" @click="displayPicker = setting.id">
                  <span class="current-color" :style="'background-color: ' + setting.value"></span>
                    <chrome-picker v-if="displayPicker == setting.id" v-model="setting.value" @input="updateFromPicker($event, setting)" />
                  </span>
                </span>
              </template>
              <template v-else-if="setting.type == 'map'">
                <input class="form-control" v-model="setting.value" @blur="saveSetting(setting)" />
              </template>
            </div>
          </b-card-body>
        </b-card>
      </template>
    </div>

    <div class="spinner" v-if="showSpinner">
      <img src="/images/loader.svg" />
      <span>Saving</span>
    </div>
  </div>
</template>

<script>
import { Chrome } from 'vue-color';
import { Switch } from '@coreui/vue';

export default {
  name: 'webchat-settings',
  props: ['id'],
  components: {
    'b-switch': Switch,
    'chrome-picker': Chrome,
  },
  data() {
    return {
      displayPicker: 0,
      showSpinner: false,
      subCategory: '',
      webchatSettings: [],
    };
  },
  watch: {
    '$route' () {
      this.fetchWebchatSetting();
      this.openNavbarDropdown();
    }
  },
  mounted() {
    this.fetchWebchatSetting();
    this.openNavbarDropdown();

    document.addEventListener('click', this.onClick);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.onClick);
  },
  methods: {
    fetchWebchatSetting() {
      this.webchatSettings = [];
      this.subCategory = '';

      axios.get('/admin/api/webchat-setting').then(
        (response) => {
          response.data.forEach((setting) => {
            if (setting.type === 'object') {
              if (this.id && setting.id == this.id) {
                switch (setting.name) {
                  case 'general':
                    this.subCategory = 'General';
                    break;
                  case 'colours':
                    this.subCategory = 'Colours';
                    break;
                  case 'comments':
                    this.subCategory = 'Comments';
                    break;
                  case 'webchatHistory':
                    this.subCategory = 'History';
                    break;
                }
              }
            } else {
              if (setting.type === 'colour' && setting.value === null) {
                setting.value = '';
              }
              if (setting.type === 'boolean') {
                setting.value = (setting.value === '1') ? true : false;
              }

              if (this.id === undefined || (this.id && setting.parent_id == this.id)) {
                this.webchatSettings.push(setting);
              }
            }
          });
        },
      );
    },
    openNavbarDropdown() {
      setTimeout(() => {
        const dropdown = document.querySelector('.sidebar-nav .nav-dropdown');
        if (dropdown) {
          dropdown.classList.add('open');
        } else {
          this.openNavbarDropdown();
        }
      }, 100);
    },
    onClick(event) {
      if (event.target.closest('.vc-chrome, .color-picker-container, input.color') === null) {
        this.displayPicker = 0;
      }
    },
    updateFromPicker(value, setting) {
      setting.value = value.hex;

      this.saveSetting(setting);
    },
    saveSetting(setting) {
      this.showSpinner = true;
      axios.patch('/admin/api/webchat-setting/' + setting.id, { value: setting.value })
        .then(() => {
          setTimeout(() => {
            this.showSpinner = false;
          }, 200);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.form-control.color {
  width: 200px;
  background-color: #fff;
}

.color-picker-container {
  position: relative;
  display: table-cell;
  padding: 6px 12px;
  font-size: 14px;
  font-weight: 400;
  line-height: 1;
  color: #555;
  text-align: center;
  background-color: #eee;
  border: 1px solid #ccc;
  border-radius: 4px;
  cursor: pointer;

  .current-color {
    display: inline-block;
    width: 16px;
    height: 16px;
    background-color: #000;
  }

  .vc-chrome {
    position: absolute;
    top: 35px;
    right: 0;
    z-index: 9;
  }
}

.spinner {
  position: fixed;
  bottom: 0;
  left: 50%;
  right: 50%;
  width: 115px;
  background: rgba(255, 255, 255, .8);
  box-shadow: #ddd 0px 0px 5px 0px;
  z-index: 9999;

  img {
    width: 50px;
    height: 50px;
  }
}
</style>
