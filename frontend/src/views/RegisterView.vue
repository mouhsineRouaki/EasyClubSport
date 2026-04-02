<script setup>
import { computed, reactive } from 'vue'
import imageHero from '../assets/hero.png'

const roles = [
  { label: 'President', value: 'president' },
  { label: 'Coach', value: 'coach' },
  { label: 'Joueur', value: 'joueur' },
]

const formulaire = reactive({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  adresse: '',
  password: '',
  password_confirmation: '',
  role: 'joueur',
})

const motDePasseDifferent = computed(() => {
  if (!formulaire.password_confirmation) {
    return false
  }

  return formulaire.password !== formulaire.password_confirmation
})

const resumeInscription = computed(() => ({
  nom: formulaire.nom,
  prenom: formulaire.prenom,
  email: formulaire.email,
  telephone: formulaire.telephone,
  adresse: formulaire.adresse,
  role: formulaire.role,
}))

const soumettre = () => {
  console.log('Donnees du formulaire register :', { ...formulaire })
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-shell">
      <div class="auth-visual">
        <div class="auth-brand">
          <span class="brand-badge">ES</span>
          <div>
            <p>EasyClubSport</p>
            <h1>Construire votre club de sport avec une base solide</h1>
          </div>
        </div>

        <img :src="imageHero" alt="Illustration sportive" class="auth-image" />

        <div class="auth-note">
          <strong>Etape actuelle</strong>
          <p>
            Nous commencons par une page register claire, avec les champs relies a Vue.
          </p>
        </div>
      </div>

      <div class="auth-panel">
        <div class="auth-panel-head">
          <p class="eyebrow">Inscription</p>
          <h2>Creer un compte</h2>
          <p class="muted">
            Cette premiere version construit uniquement l interface et la liaison des champs.
          </p>
        </div>

        <form class="auth-form" @submit.prevent="soumettre">
          <div class="form-grid two-columns">
            <label class="field">
              <span>Nom</span>
              <input v-model="formulaire.nom" type="text" placeholder="Entrer votre nom" />
            </label>

            <label class="field">
              <span>Prenom</span>
              <input v-model="formulaire.prenom" type="text" placeholder="Entrer votre prenom" />
            </label>
          </div>

          <div class="form-grid two-columns">
            <label class="field">
              <span>Email</span>
              <input v-model="formulaire.email" type="email" placeholder="exemple@email.com" />
            </label>

            <label class="field">
              <span>Telephone</span>
              <input v-model="formulaire.telephone" type="text" placeholder="06XXXXXXXX" />
            </label>
          </div>

          <label class="field">
            <span>Adresse</span>
            <input v-model="formulaire.adresse" type="text" placeholder="Ville, quartier, adresse" />
          </label>

          <label class="field">
            <span>Role</span>
            <select v-model="formulaire.role">
              <option v-for="role in roles" :key="role.value" :value="role.value">
                {{ role.label }}
              </option>
            </select>
          </label>

          <div class="form-grid two-columns">
            <label class="field">
              <span>Mot de passe</span>
              <input v-model="formulaire.password" type="password" placeholder="Mot de passe" />
            </label>

            <label class="field">
              <span>Confirmation du mot de passe</span>
              <input
                v-model="formulaire.password_confirmation"
                type="password"
                placeholder="Confirmer le mot de passe"
              />
            </label>
          </div>

          <p v-if="motDePasseDifferent" class="error-text">
            Les deux mots de passe ne sont pas identiques.
          </p>

          <button class="primary-button" type="submit">Continuer</button>
        </form>

        <section class="preview-card">
          <div class="preview-head">
            <h3>Ce que Vue fait ici</h3>
            <span>Etape 1</span>
          </div>

          <ul class="preview-list">
            <li>Chaque input est lie a `formulaire` avec `v-model`.</li>
            <li>La verification du mot de passe utilise `computed`.</li>
            <li>Le bouton submit declenche `soumettre()` sans recharger la page.</li>
          </ul>

          <pre class="preview-json">{{ JSON.stringify(resumeInscription, null, 2) }}</pre>
        </section>
      </div>
    </section>
  </main>
</template>
