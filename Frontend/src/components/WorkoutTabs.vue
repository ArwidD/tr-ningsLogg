<template>
  <div class="workout-tabs">
    <div class="tabs">
      <button 
        @click="activeTab = 'form'" 
        :class="{ active: activeTab === 'form' }">
        Lägg till pass
      </button>
      <button 
        @click="activeTab = 'list'" 
        :class="{ active: activeTab === 'list' }">
        Visa pass
      </button>
    </div>

    <div class="tab-content">
      <WorkoutForm v-if="activeTab === 'form'" @workout-saved="onWorkoutSaved" />
      <WorkoutList v-if="activeTab === 'list'" ref="workoutList" />
    </div>
  </div>
</template>

<script>
import WorkoutForm from './WorkoutForm.vue'
import WorkoutList from './WorkoutList.vue'

export default {
  name: 'WorkoutTabs',
  
  components: {
    WorkoutForm,
    WorkoutList
  },

  data() {
    return {
      activeTab: 'form'
    }
  },

  methods: {
    onWorkoutSaved() {
      this.activeTab = 'list'
      this.$nextTick(() => {
        if (this.$refs.workoutList) {
          this.$refs.workoutList.fetchWorkouts()
        }
      })
    }
  }
}
</script>