<script setup>
import { computed } from 'vue'
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'

const props = defineProps({
  disponibilites: { type: Array, default: () => [] },
  equipes: { type: Array, default: () => [] },
  equipeId: { type: String, default: '' },
  evenements: { type: Array, default: () => [] },
  evenementId: { type: String, default: '' },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:equipeId', 'update:evenementId', 'update:recherche', 'aller-convocations'])

const badgeDisponibilite = (reponse) => ({
  present: { label: 'Present', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  absent: { label: 'Absent', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  incertain: { label: 'Incertain', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
  sans_reponse: { label: 'Sans reponse', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
}[reponse] || { label: reponse || 'Sans reponse', cls: 'bg-[#f1f5f9] text-[#64748b]' })

const badgeConvocation = (statut) => ({
  convoque: { label: 'Convoque', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
  confirme: { label: 'Confirme', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  refuse: { label: 'Refuse', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  en_attente: { label: 'En attente', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
}[statut] || { label: statut || 'Aucune', cls: 'bg-[#f1f5f9] text-[#64748b]' })

const disponibilitesFiltrees = computed(() => {
  const recherche = props.recherche.trim().toLowerCase()

  if (!recherche) {
    return props.disponibilites
  }

  return props.disponibilites.filter((item) => {
    const joueur = item.joueur || {}
    return [
      joueur.prenom,
      joueur.nom,
      joueur.name,
      joueur.email,
      item.disponibilite?.commentaire,
      item.convocation?.statut,
    ]
      .filter(Boolean)
      .some((valeur) => String(valeur).toLowerCase().includes(recherche))
  })
})
</script>

<template>
  <section class="mt-6">
    <AppModuleHeader badge="Gestion coach" titre="Disponibilites"
      description="Consultez les reponses des joueurs avant de preparer vos convocations." />

    <div class="mx-auto mt-5 grid max-w-4xl gap-3 lg:grid-cols-[0.9fr_1.1fr]">
      <select :value="equipeId"
        class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
        @change="emit('update:equipeId', $event.target.value)">
        <option value="">Choisir une equipe</option>
        <option v-for="eq in equipes" :key="eq.id" :value="String(eq.id)">{{ eq.nom }}</option>
      </select>

      <select :value="evenementId"
        class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
        @change="emit('update:evenementId', $event.target.value)">
        <option value="">Choisir un evenement</option>
        <option v-for="evenement in evenements" :key="evenement.id" :value="String(evenement.id)">
          {{ evenement.titre }} - {{ evenement.date_debut ? new Date(evenement.date_debut).toLocaleDateString('fr-FR') :
          'Date non definie' }}
        </option>
      </select>
    </div>

    <div class="mx-auto mt-3 max-w-3xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
      <input :value="recherche" type="text" placeholder="Rechercher un joueur ou un commentaire..."
        class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
        @input="emit('update:recherche', $event.target.value)" />
    </div>

    <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="n in 6" :key="n"
        class="h-[250px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="disponibilitesFiltrees.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <article v-for="item in disponibilitesFiltrees" :key="item.utilisateur_id"
        class="group relative overflow-hidden rounded-[26px] border border-[#edf1f7] bg-white p-4 text-left transition duration-300 hover:-translate-y-1 hover:border-[#d7e0f5] hover:bg-[#fbfcff]">
        <div class="flex items-start justify-between gap-3">
          <div class="flex min-w-0 items-center gap-3">
            <span
              class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-sm font-black text-white">
              <img v-if="item.joueur?.photo_url" :src="item.joueur.photo_url" :alt="item.joueur?.name || 'Joueur'"
                class="h-full w-full object-cover" />
              <span v-else>{{ (item.joueur?.prenom || item.joueur?.name || 'J').slice(0, 1) }}</span>
            </span>

            <div class="min-w-0">
              <p class="truncate text-[10px] font-black uppercase tracking-[0.14em] text-[#6b7280]">Joueur</p>
              <h4 class="mt-2 truncate text-lg font-black leading-tight text-[#111827]">
                {{ [item.joueur?.prenom, item.joueur?.nom].filter(Boolean).join(' ') || item.joueur?.name || 'Joueur' }}
              </h4>
              <p class="mt-1 truncate text-xs font-semibold text-[#64748b]">{{ item.joueur?.email || 'Email non defini'
                }}</p>
            </div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <span class="rounded-full px-3 py-1.5 text-[10px] font-black"
              :class="badgeDisponibilite(item.disponibilite?.reponse || 'sans_reponse').cls">
              {{ badgeDisponibilite(item.disponibilite?.reponse || 'sans_reponse').label }}
            </span>
            <span class="rounded-full px-3 py-1.5 text-[10px] font-black"
              :class="badgeConvocation(item.convocation?.statut).cls">
              {{ badgeConvocation(item.convocation?.statut).label }}
            </span>
          </div>
        </div>

        <div class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
          <p class="text-[10px] font-black uppercase tracking-[0.14em] text-[#6b7280]">Commentaire du joueur</p>
          <p class="mt-2 line-clamp-3 text-sm font-semibold leading-6 text-[#64748b]">
            {{ item.disponibilite?.commentaire || 'Aucun commentaire.' }}
          </p>
        </div>

        <div class="mt-4 flex items-center justify-between gap-2">
          <p class="text-[11px] font-bold text-[#94a3b8]">
            Affecte le {{ item.date_affectation ? new Date(item.date_affectation).toLocaleDateString('fr-FR') : '-' }}
          </p>
          <AppButton type="button" size="sm" @click="emit('aller-convocations')">
            Aller aux convocations
          </AppButton>
        </div>

        <div
          class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12">
        </div>
      </article>
    </div>

    <div v-else-if="evenementId"
      class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucune disponibilite</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Les joueurs n ont pas encore repondu a cet evenement.</p>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#6b7280]">Selectionnez un evenement pour voir les disponibilites.</p>
    </div>
  </section>
</template>
