<template>
  <form @submit.prevent="submitWorkout">
    <label>
      Datum:
      <input type="date" v-model="workout.date" required />
    </label>
    <button type="submit">Lägg till pass</button>
  </form>
</template>
<script>
export default {
  data() {
    return {
      workout: {
        date: ''
      }
    }
  },
  methods: {
    submitWorkout() {
      console.log(this.workout);
    }
  }
}
</script>
