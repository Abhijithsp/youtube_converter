<template>
  <div class="w-full">
    <div class="flex justify-between items-center mb-2">
      <span class="text-sm font-medium text-gray-300">{{ label }}</span>
      <span class="text-sm font-bold text-purple-400">{{ progress }}%</span>
    </div>
    <div class="w-full bg-gray-700/50 rounded-full h-3 overflow-hidden backdrop-blur-sm">
      <div 
        class="h-full rounded-full transition-all duration-500 ease-out bg-gradient-to-r from-purple-500 via-pink-500 to-purple-600 relative overflow-hidden"
        :style="{ width: progress + '%' }"
      >
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProgressBar',
  props: {
    progress: {
      type: Number,
      required: true,
      default: 0,
    },
    label: {
      type: String,
      default: 'Progress',
    },
  },
}
</script>

<style scoped>
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 2s infinite;
}
</style>
