<script setup>
import AppAvatar from '@/shared/components/ui/AppAvatar.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import PlayerGameCard from '@/roles/coach/joueurs/components/PlayerGameCard.vue'

const props = defineProps({
  joueur: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['edit', 'remove'])

const nomComplet = (joueur) =>
  [joueur?.prenom, joueur?.nom_famille].filter(Boolean).join(' ') ||
  joueur?.nom ||
  joueur?.name ||
  'Joueur'
</script>

<template>
  <div class="space-y-5">
    <PlayerGameCard :joueur="joueur" />

    <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
      <section class="rounded-[28px] border border-[#dbe5f2] bg-white p-5 shadow-[0_18px_40px_rgba(15,23,42,0.06)]">
        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#4c6fff]">Informations</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          <div class="rounded-[22px] border border-[#e6edf8] bg-[#f8fbff] p-4">
            <p class="text-[11px] font-black uppercase tracking-[0.14em] text-[#64748b]">Email</p>
            <p class="mt-2 text-sm font-semibold text-[#0f172a]">{{ joueur.email || '-' }}</p>
          </div>
          <div class="rounded-[22px] border border-[#e6edf8] bg-[#f8fbff] p-4">
            <p class="text-[11px] font-black uppercase tracking-[0.14em] text-[#64748b]">Telephone</p>
            <p class="mt-2 text-sm font-semibold text-[#0f172a]">{{ joueur.telephone || '-' }}</p>
          </div>
          <div class="rounded-[22px] border border-[#e6edf8] bg-[#f8fbff] p-4 sm:col-span-2">
            <p class="text-[11px] font-black uppercase tracking-[0.14em] text-[#64748b]">Adresse</p>
            <p class="mt-2 text-sm font-semibold text-[#0f172a]">{{ joueur.adresse || 'Non definie' }}</p>
          </div>
        </div>
      </section>

      <section class="rounded-[28px] border border-[#dbe5f2] bg-white p-5 shadow-[0_18px_40px_rgba(15,23,42,0.06)]">
        <div class="flex items-center gap-3">
          <AppAvatar
            :src="joueur.photo_url || joueur.photo || ''"
            :alt="nomComplet(joueur)"
            :initials="nomComplet(joueur).slice(0, 2).toUpperCase()"
            size-class="h-14 w-14"
            rounded-class="rounded-[18px]"
          />
          <div>
            <p class="text-lg font-black text-[#0f172a]">{{ nomComplet(joueur) }}</p>
            <p class="text-sm font-semibold text-[#64748b]">{{ joueur.poste_principal || 'Poste a definir' }}</p>
          </div>
        </div>

        <div class="mt-5 grid gap-3">
          <AppButton type="button" block @click="emit('edit', joueur)">Modifier</AppButton>
          <AppButton type="button" variant="danger" block @click="emit('remove', joueur)">Retirer de l equipe</AppButton>
        </div>
      </section>
    </div>
  </div>
</template>
