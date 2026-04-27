<script setup>
import AppCard from '@/shared/components/AppCard.vue'
import AppProfileManager from '@/shared/components/AppProfileManager.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import PresidentProfileModuleCard from '@/roles/president/profil/components/PresidentProfileModuleCard.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'

const { utilisateur, deconnecter, sauvegarderUtilisateur } = useAuthSession()

const parametresCompte = [
  'Recevoir les alertes des nouvelles inscriptions',
  'Recevoir les reponses des coachs',
  'Notifier quand un document est ajoute',
]

const parametresApplication = [
  'Activer les rappels des evenements',
  'Recevoir le resume mensuel du club',
  'Synchroniser les annonces importantes',
]

const conversations = [
  { nom: 'Coach principal', message: 'Planning entrainement a valider.', couleur: '#fbbf24' },
  { nom: 'Responsable joueurs', message: 'Deux nouveaux joueurs a confirmer.', couleur: '#ef4444' },
  { nom: 'Documents club', message: 'Un nouveau document interne a ete ajoute.', couleur: '#334155' },
  { nom: 'Equipe U19', message: 'Match amical propose samedi.', couleur: '#0ea5e9' },
]

const modules = [
  { titre: 'Gestion des equipes', description: 'Creer et suivre les equipes du club.', image: 'linear-gradient(135deg,#0f172a,#1e293b)' },
  { titre: 'Evenements sportifs', description: 'Planifier matchs, reunions et stages.', image: 'linear-gradient(135deg,#1d4ed8,#0ea5e9)' },
  { titre: 'Documents du club', description: 'Centraliser les fichiers et pieces partagees.', image: 'linear-gradient(135deg,#14532d,#16a34a)' },
]

const synchroniserProfil = (payload) => {
  const utilisateurMisAJour = payload?.utilisateur || payload?.data?.utilisateur || null

  if (utilisateurMisAJour) {
    sauvegarderUtilisateur(utilisateurMisAJour)
  }
}
</script>

<template>
  <PresidentShellLayout
    breadcrumb="Profil"
    title="Profil president"
    active-section="profil"
    :utilisateur="utilisateur"
    @logout="deconnecter"
  >
    <AppProfileManager :visible="true" role-label="President" profile-endpoint="/president/profil" @saved="synchroniserProfil" />

    <section class="mt-4 grid gap-4 xl:grid-cols-[0.95fr_0.95fr_1.1fr]">
      <AppCard title="Parametres" subtitle="Preferences du compte president.">
        <p class="text-xs font-bold uppercase text-[#64748b]">Compte</p>
        <div class="mt-3 space-y-3">
          <label v-for="(parametre, index) in parametresCompte" :key="parametre" class="flex items-center gap-3 text-xs text-[#475569]">
            <span :class="['relative h-4 w-8 rounded-full transition', index === 1 ? 'bg-[#d1d5db]' : 'bg-[#334155]']">
              <span :class="['absolute top-0.5 h-3 w-3 rounded-full bg-white transition', index === 1 ? 'left-0.5' : 'left-[18px]']"></span>
            </span>
            {{ parametre }}
          </label>
        </div>

        <p class="mt-6 text-xs font-bold uppercase text-[#64748b]">Application</p>
        <div class="mt-3 space-y-3">
          <label v-for="(parametre, index) in parametresApplication" :key="parametre" class="flex items-center gap-3 text-xs text-[#475569]">
            <span :class="['relative h-4 w-8 rounded-full transition', index === 1 ? 'bg-[#d1d5db]' : 'bg-[#334155]']">
              <span :class="['absolute top-0.5 h-3 w-3 rounded-full bg-white transition', index === 1 ? 'left-0.5' : 'left-[18px]']"></span>
            </span>
            {{ parametre }}
          </label>
        </div>
      </AppCard>

      <AppCard title="Messages recents" subtitle="Echanges internes du club.">
        <div class="space-y-3">
          <div v-for="conversation in conversations" :key="conversation.nom" class="flex items-center gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-xs font-bold text-white" :style="{ background: conversation.couleur }">
              {{ conversation.nom.slice(0, 2).toUpperCase() }}
            </span>
            <div class="min-w-0 flex-1">
              <p class="truncate text-xs font-bold text-[#1f2a44]">{{ conversation.nom }}</p>
              <p class="truncate text-xs text-[#64748b]">{{ conversation.message }}</p>
            </div>
            <AppButton type="button" variant="ghost" size="xs">Ouvrir</AppButton>
          </div>
        </div>
      </AppCard>

      <AppCard title="Modules president" subtitle="Acces rapide aux axes de gestion.">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-1">
          <PresidentProfileModuleCard v-for="module in modules" :key="module.titre" :module="module" />

          <AppButton type="button" variant="ghost" class="grid min-h-[156px] w-full place-items-center rounded-xl border-dashed text-center text-xs font-bold text-[#64748b] hover:!border-[#2563eb] hover:!text-[#2563eb]">
            <span>
              <span class="block text-xl">+</span>
              Ajouter un module
            </span>
          </AppButton>
        </div>
      </AppCard>
    </section>
  </PresidentShellLayout>
</template>
