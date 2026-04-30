<template>
  <div>
    <label class="block text-sm font-medium text-gray-300 mb-2">Audio Format</label>
    <div class="grid grid-cols-2 gap-3">
      <button
        v-for="format in formats"
        :key="format.value"
        @click="selectFormat(format.value)"
        class="relative p-4 rounded-lg border-2 transition-all duration-200 hover:scale-105"
        :class="modelValue === format.value 
          ? 'border-purple-500 bg-purple-500/20 shadow-lg shadow-purple-500/50' 
          : 'border-gray-600 bg-gray-800/50 hover:border-gray-500'"
      >
        <div class="flex flex-col items-center space-y-2">
          <svg class="w-8 h-8" :class="modelValue === format.value ? 'text-purple-400' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
          </svg>
          <span class="font-semibold uppercase" :class="modelValue === format.value ? 'text-purple-300' : 'text-gray-300'">
            {{ format.label }}
          </span>
          <span class="text-xs text-gray-400">{{ format.description }}</span>
        </div>
        <div 
          v-if="modelValue === format.value"
          class="absolute top-2 right-2"
        >
          <svg class="w-5 h-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormatSelector',
  props: {
    modelValue: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      formats: [
        { value: 'mp3', label: 'MP3', description: 'Universal compatibility' },
        { value: 'flac', label: 'FLAC', description: 'Lossless quality' },
      ],
    };
  },
  methods: {
    selectFormat(format) {
      this.$emit('update:modelValue', format);
    },
  },
}
</script>
