<template>
  <div class="workout-list">
    <h2>Mina Träningspass</h2>

    <div class="filters">
      <label>
        Från datum:
        <input type="date" v-model="filters.from" @change="fetchWorkouts" />
      </label>
      <label>
        Till datum:
        <input type="date" v-model="filters.to" @change="fetchWorkouts" />
      </label>
      <button @click="clearFilters">Rensa filter</button>
    </div>

    <button @click="downloadPDF" v-if="workouts.length > 0">
      Ladda ner som PDF
    </button>

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
            <th>Åtgärder</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="workout in workouts" :key="workout.id">
            <td>{{ formatDate(workout.date) }}</td>
            <td>{{ workout.activity }}</td>
            <td>{{ workout.difficulty }}/10</td>
            <td>{{ workout.distance ? workout.distance + ' km' : '-' }}</td>
            <td>{{ workout.duration ? workout.duration + ' min' : '-' }}</td>
            <td>
              <button @click="deleteWorkout(workout.id)">Ta bort</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { jsPDF } from 'jspdf'

export default {
  name: 'WorkoutList',
  
  data() {
    return {
      workouts: [],
      loading: false,
      error: null,
      filters: {
        from: '',
        to: ''
      }
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
        // Bygg URL med filter
        let url = '/api/workouts'
        const params = new URLSearchParams()
        
        if (this.filters.from) {
          params.append('from', this.filters.from)
        }
        if (this.filters.to) {
          params.append('to', this.filters.to)
        }
        
        if (params.toString()) {
          url += '?' + params.toString()
        }

        const response = await fetch(url)
        
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
    },

    downloadPDF() {
      const doc = new jsPDF()

      // Titel
      doc.setFontSize(18)
      doc.text('Träningslogg', 14, 20)

      // Datum för rapporten
      doc.setFontSize(10)
      doc.text(`Genererad: ${new Date().toLocaleDateString('sv-SE')}`, 14, 28)

      // Statistik
      const totalWorkouts = this.workouts.length
      const totalDistance = this.workouts.reduce((sum, w) => sum + (parseFloat(w.distance) || 0), 0)
      const totalDuration = this.workouts.reduce((sum, w) => sum + (parseInt(w.duration) || 0), 0)
      const avgDifficulty = this.workouts.reduce((sum, w) => sum + w.difficulty, 0) / totalWorkouts

      doc.setFontSize(12)
      doc.text('Statistik', 14, 38)
      doc.setFontSize(10)
      doc.text(`Antal pass: ${totalWorkouts}`, 14, 45)
      doc.text(`Total distans: ${totalDistance.toFixed(1)} km`, 14, 51)
      doc.text(`Total tid: ${totalDuration} min`, 14, 57)
      doc.text(`Genomsnittlig ansträngning: ${avgDifficulty.toFixed(1)}/10`, 14, 63)

      // Tabell med träningspass
      doc.setFontSize(12)
      doc.text('Träningspass', 14, 73)

      let y = 82
      doc.setFontSize(9)
      
      // Tabellhuvud
      doc.setFont(undefined, 'bold')
      doc.text('Datum', 14, y)
      doc.text('Aktivitet', 45, y)
      doc.text('Anstr.', 85, y)
      doc.text('Distans', 110, y)
      doc.text('Tid', 145, y)
      
      y += 6
      doc.setFont(undefined, 'normal')

      // Tabellrader
      this.workouts.forEach(workout => {
        if (y > 270) {
          doc.addPage()
          y = 20
        }

        doc.text(this.formatDate(workout.date), 14, y)
        doc.text(workout.activity, 45, y)
        doc.text(`${workout.difficulty}/10`, 85, y)
        doc.text(workout.distance ? `${workout.distance} km` : '-', 110, y)
        doc.text(workout.duration ? `${workout.duration} min` : '-', 145, y)
        
        y += 6
      })

      // Spara PDF
      doc.save(`traningslogg_${new Date().toISOString().split('T')[0]}.pdf`)
    },

    async deleteWorkout(id) {
      if (!confirm('Är du säker på att du vill ta bort detta pass?')) {
        return
      }

      try {
        const response = await fetch(`/api/workouts/${id}`, {
          method: 'DELETE'
        })

        if (response.ok) {
          console.log('Workout borttaget')
          // Uppdatera listan
          this.fetchWorkouts()
        } else {
          console.error('Fel vid borttagning:', response.status)
          alert('Kunde inte ta bort passet')
        }
      } catch (err) {
        console.error('Fetch error:', err)
        alert('Fel vid borttagning')
      }
    },

    clearFilters() {
      this.filters.from = ''
      this.filters.to = ''
      this.fetchWorkouts()
    }
  }
}
</script>