<template>
  <main class="relative min-h-screen overflow-hidden bg-[radial-gradient(120%_90%_at_50%_0%,#172554_0%,#1d4ed8_46%,#14b8a6_100%)] text-white">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-28 top-16 h-72 w-72 rounded-full bg-[#8db2ff]/18 blur-3xl"></div>
      <div class="absolute right-[-90px] top-[-30px] h-80 w-80 rounded-full bg-[#5eead4]/14 blur-3xl"></div>
      <div class="absolute inset-x-[8%] top-[24%] h-[420px] rounded-[999px] border border-white/14"></div>
      <div class="absolute inset-x-[16%] top-[30%] h-[320px] rounded-[999px] border border-white/12"></div>
      <div class="absolute inset-x-[24%] top-[36%] h-[220px] rounded-[999px] border border-white/10"></div>
    </div>

    <div class="relative mx-auto max-w-[1380px] px-4 pb-8 pt-4 sm:px-6 sm:pt-6 lg:px-8">
      <slot />
    </div>
  </main>
</template>
