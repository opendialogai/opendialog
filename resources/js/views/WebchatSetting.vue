<template>
  <div class="mt-4">
    <template v-for="setting in webchatSettings">
      <div class="card mb-2 p-0" v-if="setting.type !== 'object'">
        <div class="card-body pt-2 pb-2 pl-4 pr-4 row">
          <div class="card-title mt-2 mb-0 col-6">{{ setting.name }}</div>
          <div class="card-text col-6 row">
            <template v-if="setting.type == 'boolean'">
              <b-switch variant="pill" color="dark" :checked="setting.value === '1'" />
            </template>
            <template v-else-if="setting.type == 'string'">
              <input class="form-control" v-model="setting.value" />
            </template>
            <template v-else-if="setting.type == 'number'">
              <input class="form-control" type="number" v-model="setting.value" />
            </template>
            <template v-else-if="setting.type == 'colour'">
              <input type="text" class="form-control color" :value="setting.value" @focus="displayPicker = setting.id" />
              <span class="color-picker-container">
                <span class="current-color" :style="'background-color: ' + setting.value" @click="displayPicker = setting.id"></span>
                  <chrome-picker v-if="displayPicker == setting.id" v-model="setting.value" @input="updateFromPicker($event, setting)" />
                </span>
              </span>
            </template>
            <template v-else-if="setting.type == 'map'">
              <input class="form-control" v-model="setting.value" />
            </template>
          </div>
        </div>
      </div>
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
  },
  methods: {
    updateFromPicker(value, setting) {
      setting.value = value.hex;
    },
  },
};
</script>

<style lang="scss" scoped>
.card {
  border-radius: 15px;
  box-shadow: 0 3px 3px 0 #8e8e8e;
}

.form-control.color {
  width: 200px;
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

  .current-color {
    display: inline-block;
    width: 16px;
    height: 16px;
    background-color: #000;
    cursor: pointer;
  }

  .vc-chrome {
    position: absolute;
    top: 35px;
    right: 0;
    z-index: 9;
  }
}
</style>
