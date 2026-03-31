<script setup>
const frameworkName = 'Vue 3'
const currentStep = 'Initialisation du projet'

const nextSteps = [
  'Comprendre la structure du projet',
  'Creer un composant simple',
  'Apprendre les bases: template, script, style',
  'Puis consommer l API pas a pas',
]
</script>

<template>
  <main class="container">
    <section class="card">
      <p class="label">Demarrage</p>
      <h1>Bienvenue dans {{ frameworkName }}</h1>
      <p class="description">
        Cette version est volontairement simple pour apprendre Vue.js depuis zero,
        sans code API ni logique avancee.
      </p>

      <div class="status-box">
        <span>Etape actuelle</span>
        <strong>{{ currentStep }}</strong>
      </div>

      <h2>Ce qu on va apprendre ensuite</h2>
      <ul>
        <li v-for="step in nextSteps" :key="step">{{ step }}</li>
      </ul>
    </section>
  </main>
</template>
