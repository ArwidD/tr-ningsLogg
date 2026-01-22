<template>
  <form @submit.prevent="submitWorkout">
    <label>
      Datum:
      <input type="date" v-model="workout.date" required />
    </label>
    <br />
    <label>
      Aktivitet:
      <input type="text" v-model="workout.activity" required />
    </label>
    <br />
    <label>
      Ansträngning:
      <input type="number" v-model="workout.difficulty" min="1" max="10" required />
    </label>
    <br />
    <label>
      Distans (km):
      <input type="number" step="0.1" v-model="workout.distance" />
    </label>
    <br />
    <label>
      Tid (minuter):
      <input type="number" v-model="workout.duration" />
    </label>
    <br />
    <button type="submit">Lägg till pass</button>
  </form>
</template>

<script>
export default {
  data() {
    return {
      workout: {
        date: '',
        activity: '',
        difficulty: 1,
        distance: null,
        duration: null
      }
    }
  },
  methods: {
    async submitWorkout() {
      try {
        const response = await fetch('/api/workouts', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.workout)
        })

        if (response.ok) {
          const data = await response.json()
          console.log('Workout sparad:', data)

          // Skicka event till parent
          this.$emit('workout-saved')

          // Töm formuläret
          this.workout.date = ''
          this.workout.activity = ''
          this.workout.difficulty = 1
          this.workout.distance = null
          this.workout.duration = null
        } else {
          console.error('Fel vid sparande:', response.status)
        }
      } catch (err) {
        console.error('Fetch error:', err)
      }
    }
  }
}
</script>