<template>
  <div class="mt-4">
    <template v-for="setting in webchatSettings">
      <b-card no-body v-if="setting.type !== 'object'">
        <b-card-body class="pt-2 pb-2 pl-4 pr-4 row">
          <div class="card-title mt-2 mb-0 col-6">{{ setting.name }}</div>
          <div class="card-text col-6 row">
            <template v-if="setting.type == 'boolean'">
              <b-switch variant="pill" color="dark" :checked="setting.value === '1'" />
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
</template>

<script>
import { Chrome } from 'vue-color';
import { Switch } from '@coreui/vue';

export default {
  name: 'webchat-setting',
  components: {
    'b-switch': Switch,
    'chrome-picker': Chrome,
  },
  data() {
    return {
      displayPicker: 0,
      webchatSettings: [],
    };
  },
  mounted() {
    axios.get('/admin/api/webchat-setting').then(
      (response) => {
        this.webchatSettings = response.data;

        this.webchatSettings.forEach((setting, i) => {
          if (setting.type === 'colour' && setting.value === null) {
            this.webchatSettings[i].value = '';
          }
        });
      },
    );

    document.addEventListener('click', this.onClick);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.onClick);
  },
  methods: {
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
      axios.patch('/admin/api/webchat-setting/' + setting.id, { value: setting.value });
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
</style>
