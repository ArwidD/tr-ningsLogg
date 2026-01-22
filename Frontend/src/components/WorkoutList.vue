<template>
  <div class="workout-list">
    <h2>Mina Träningspass</h2>

    <div v-if="loading">
      Laddar träningspass...
    </div>

    <div v-else-if="error">
      Fel: {{ error }}
    </div>

    <div v-else-if="workouts.length === 0">
      Inga träningspass ännu. Lägg till ditt första pass!
    </div>

    <div v-else>
      <table>
        <thead>
          <tr>
            <th>Datum</th>
            <th>Aktivitet</th>
            <th>Ansträngning</th>
            <th>Distans</th>
            <th>Tid</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="workout in workouts" :key="workout.id">
            <td>{{ formatDate(workout.date) }}</td>
            <td>{{ workout.activity }}</td>
            <td>{{ workout.difficulty }}/10</td>
            <td>{{ workout.distance ? workout.distance + ' km' : '-' }}</td>
            <td>{{ workout.duration ? workout.duration + ' min' : '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WorkoutList',
  
  data() {
    return {
      workouts: [],
      loading: false,
      error: null
    }
  },

  mounted() {
    this.fetchWorkouts()
  },

  methods: {
    async fetchWorkouts() {
      this.loading = true
      this.error = null

      try {
        const response = await fetch('/api/workouts')
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        
        const data = await response.json()
        this.workouts = data
        
      } catch (err) {
        console.error('Fetch error:', err)
        this.error = err.message
      } finally {
        this.loading = false
      }
    },

    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('sv-SE')
    }
  }
}
</script>