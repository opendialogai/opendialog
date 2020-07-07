<template>
  <div class="form-message">
    <div class="form-message--text mb-1" v-if="message.data.text">{{ message.data.text }}</div>

    <div v-for="element in message.data.elements" class="form-message--element mb-2">
      <span v-if="element.display" class="form-message--element-label">{{ element.display }}:</span>

      <template v-if="element.element_type == 'text'">
        <input class="form-message--element-input" :value="element.default_value" />
      </template>
      <template v-if="element.element_type == 'number'">
        <input type="number" class="form-message--element-input" :value="element.default_value" />
      </template>
      <template v-if="element.element_type == 'email'">
        <input type="email" class="form-message--element-input" :value="element.default_value" />
      </template>
      <template v-if="element.element_type == 'textarea'">
        <textarea class="form-message--element-textarea" :value="element.default_value" />
      </template>
      <template v-if="element.element_type == 'select'">
        <select class="form-message--element-select">
          <option v-for="option in element.options" v-bind:value="option.key">
            {{ option.value }}
          </option>
        </select>
      </template>
      <template v-if="element.element_type == 'auto_complete_select'">
        <v-select :options="element.options" :reduce="option => option.key" label="value"></v-select>
      </template>
      <template v-if="element.element_type == 'radio'">
        <div class="form-message--radio">
          <div class="form-message--radio-btn" v-for="option in element.options" :key="option.key">
            <input
              name="radio"
              type="radio"
              :checked="(option.key == element.default_value) ? true : false" />
            <label>{{ option.value }}</label>
          </div>
        </div>
      </template>
    </div>
    <button class="btn btn-default btn-primary">{{ message.data.submit_text }}</button>
  </div>
</template>

<script>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

export default {
  name: 'form-message',
  components: {
    vSelect,
  },
  props: ['message'],
};
</script>

<style lang="scss" scoped>
.message {
  .form-message--radio {
    display: flex;

    .form-message--radio-btn {
      margin-right: 22px;
    }
  }

  .v-select {
    display: inline-block;
    width: 150px;
  }
}
</style>
