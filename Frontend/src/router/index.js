import { createRouter, createWebHistory } from 'vue-router'
import WorkoutForm from '../components/WorkoutForm.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: WorkoutForm, 
    },
  ],
})


export default router
