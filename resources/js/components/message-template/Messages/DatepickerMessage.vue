<template>
  <div class="datepicker-message">
    <p>{{ message.data.text }}</p>
    <div class="datepicker-message__wrapper">
      <select v-model="selectedDay" :required="message.data.days_required" class="datepicker-message__dropdown">
        <option :value="null">- Day -</option>
        <option v-for="(day, i) in days" :key="i" :value="day">{{ day }}</option>
      </select>
      <select v-model="selectedMonth" :required="message.data.month_required" class="datepicker-message__dropdown">
        <option :value="null">- Month -</option>
        <option v-for="(month, i) in months" :key="i" :value="month">{{ month }}</option>
      </select>
      <select v-model="selectedYear" :required="message.data.year_required" class="datepicker-message__dropdown">
        <option :value="null">- Year -</option>
        <option v-for="(year, i) in years" :key="i" :value="year">{{ year }}</option>
      </select>
      <input v-if="message.data.day_required" type="date" class="datepicker-message__mobile-picker" :min="minStr" :max="maxStr" @change="dateSelected($event.target.value)" pattern="\d{4}-\d{2}-\d{2}">
      <input v-else type="month" class="datepicker-message__mobile-picker" :min="minStr" :max="maxStr" @change="dateSelected($event.target.value)" pattern="[0-9]{4}-[0-9]{2}">
    </div>
    <button class="btn btn-default btn-primary mt-2" :disabled="!valid">{{ message.data.submit_text }}</button>
  </div>
</template>

<script>
import moment from 'moment';

export default {
  name: 'datepicker-message',
  props: ['message'],
  data() {
    return {
      selectedDay: null,
      selectedMonth: null,
      selectedYear: null,
      minDate: this.message.data.min_date === 'today' ? moment() : moment(this.message.data.min_date, 'YYYY-MM-DD'),
      maxDate: this.message.data.max_date === 'today' ? moment() : moment(this.message.data.max_date, 'YYYY-MM-DD'),
      selectedDate: null,
    };
  },
  computed: {
    years() {
      const maxYear = this.maxDate.year();
      const minYear = this.minDate.year();
      const diff = maxYear - minYear + 1;

      return Array.from({length: diff}, (v, i) => maxYear - diff + i + 1 + '').reverse();
    },
    months() {
      let months = []

      if (parseInt(this.selectedYear) === this.minDate.year() 
        && parseInt(this.selectedYear) === this.maxDate.year()) {
        months = moment.months().slice(this.minDate.month(), this.maxDate.month() + 1);
      } else if (parseInt(this.selectedYear) === this.minDate.year()) {
        months = moment.months().slice(this.minDate.month());
      } else if (parseInt(this.selectedYear) === this.maxDate.year()) {
        months = moment.months().slice(0, this.maxDate.month() + 1);
      } else {
        months = moment.months();
      }

      if (!months.includes(this.selectedMonth)) {
        this.selectedMonth = null
      }

      return months
    },
    days() {
      let arr = this.constructDayArray()

      if (moment(this.selectedMonth, 'MMMM').month() === this.maxDate.month() && parseInt(this.selectedYear) === this.maxDate.year()) {
        arr = arr.slice(0, this.maxDate.date())
      }

      if (moment(this.selectedMonth, 'MMMM').month() === this.minDate.month() && parseInt(this.selectedYear) === this.minDate.year()) {
        arr = arr.slice(this.minDate.date() -1, arr.length)
      }

      if (!arr.includes(parseInt(this.selectedDay))) {
        this.selectedDay = null
      }

      return arr
    },
    valid() {
      if (this.message.data.day_required || this.selectedDay !== null) {
        return moment([this.selectedYear, moment(this.selectedMonth, 'MMMM').month(), this.selectedDay]).isValid()
      } else if (this.message.data.month_required) {
        return (this.selectedMonth !== null && this.selectedYear !== null)
      } else {
        return this.selectedYear !== null
      }
    },
    minStr() {
      return this.message.data.day_required ? this.minDate.format('YYYY-MM-DD') : this.minDate.format('YYYY-MM')
    },
    maxStr() {
      return this.message.data.day_required ? this.maxDate.format('YYYY-MM-DD') : this.maxDate.format('YYYY-MM')
    },
  },
  methods: {
    constructDayArray() {
      let arr = []

      if (this.selectedYear && this.selectedMonth) {
        let dayCount = moment(`${this.selectedMonth}-${this.selectedYear}`, 'MMMM-YYYY').daysInMonth() + 1
        arr = [...Array(dayCount).keys()].slice(1)
      } else if (this.selectedMonth) {
        let dayCount = moment(this.selectedMonth, 'MMMM').daysInMonth() + 1
        arr = [...Array(dayCount).keys()].slice(1)
      } else {
        arr = [...Array(32).keys()].slice(1)
      }

      return arr
    },
    dateSelected(val) {
      const date = moment(val, 'YYYY-MM-DD')
      this.selectedDay = date.date()
      this.selectedMonth = date.format('MMMM')
      this.selectedYear = date.year()
    },
  },
};
</script>

<style lang="scss" scoped>
.datepicker-message {
  align-items: center;
  position: relative;
  display: flex;
  flex-direction: column;

  p {
    font-weight: 500;
    margin-bottom: 10px;
    text-align: left;
  }

  .datepicker-message__mobile-picker {
    height: 100%;
    left: 0;
    opacity: 0;
    position: absolute;
    top: 0;
    width: 100%;

    &::-webkit-calendar-picker-indicator {
      background: transparent;
      left: 0;
      position: absolute;
      top: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }
  }

  .datepicker-message__wrapper {
    position: relative;
    display: flex;
    justify-content: space-between;

    select {
      flex: 1 0 auto;
      max-width: 32%;
    }
  }
}
</style>
