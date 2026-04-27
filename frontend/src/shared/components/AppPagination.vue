<script setup>
const props = defineProps({
  pagination: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['change-page', 'change-per-page'])

const perPageOptions = [6, 12, 24, 48]

const onPage = (page) => {
  if (!props.pagination) {
    return
  }

  if (page < 1 || page > props.pagination.last_page || page === props.pagination.current_page) {
    return
  }

  emit('change-page', page)
}
</script>

<template>
  <div v-if="pagination && pagination.last_page > 0"
    class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#e8edf5] bg-[#f8fbff] px-3 py-2.5">
    <div class="text-xs text-[#64748b]">
      Affichage
      <span class="font-semibold text-[#1f2a44]">{{ pagination.from || 0 }}</span>
      a
      <span class="font-semibold text-[#1f2a44]">{{ pagination.to || 0 }}</span>
      sur
      <span class="font-semibold text-[#1f2a44]">{{ pagination.total || 0 }}</span>
    </div>

    <div class="flex items-center gap-2">
      <label class="text-xs text-[#64748b]">
        Par page
        <select
          class="ml-1 rounded-md border border-[#cbd5e1] bg-white px-2 py-1 text-xs font-semibold text-[#1f2a44] outline-none"
          :value="pagination.per_page" @change="emit('change-per-page', Number($event.target.value))">
          <option v-for="size in perPageOptions" :key="size" :value="size">{{ size }}</option>
        </select>
      </label>

      <button type="button"
        class="rounded-md border border-[#cbd5e1] bg-white px-2.5 py-1 text-xs font-semibold text-[#334155] transition hover:bg-[#f1f5f9] disabled:cursor-not-allowed disabled:opacity-40"
        :disabled="pagination.current_page <= 1" @click="onPage(pagination.current_page - 1)">
        Prec
      </button>
      <span class="text-xs font-semibold text-[#334155]">{{ pagination.current_page }} / {{ pagination.last_page
      }}</span>
      <button type="button"
        class="rounded-md border border-[#cbd5e1] bg-white px-2.5 py-1 text-xs font-semibold text-[#334155] transition hover:bg-[#f1f5f9] disabled:cursor-not-allowed disabled:opacity-40"
        :disabled="pagination.current_page >= pagination.last_page" @click="onPage(pagination.current_page + 1)">
        Suiv
      </button>
    </div>
  </div>
</template>