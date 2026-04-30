<template>
  <div>
    <label class="block text-sm font-medium text-gray-300 mb-2">Audio Quality</label>
    <div class="grid grid-cols-3 gap-3">
      <button
        v-for="quality in qualities"
        :key="quality.value"
        @click="selectQuality(quality.value)"
        class="relative p-4 rounded-lg border-2 transition-all duration-200 hover:scale-105"
        :class="modelValue === quality.value 
          ? 'border-purple-500 bg-purple-500/20 shadow-lg shadow-purple-500/50' 
          : 'border-gray-600 bg-gray-800/50 hover:border-gray-500'"
      >
        <div class="flex flex-col items-center space-y-1">
          <span class="text-2xl font-bold" :class="modelValue === quality.value ? 'text-purple-300' : 'text-gray-300'">
            {{ quality.value }}
          </span>
          <span class="text-xs text-gray-400">kbps</span>
          <span class="text-xs font-medium" :class="modelValue === quality.value ? 'text-purple-400' : 'text-gray-500'">
            {{ quality.label }}
          </span>
        </div>
        <div 
          v-if="modelValue === quality.value"
          class="absolute top-2 right-2"
        >
          <svg class="w-4 h-4 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QualitySelector',
  props: {
    modelValue: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      qualities: [
        { value: 192, label: 'Good' },
        { value: 256, label: 'Better' },
        { value: 320, label: 'Best' },
      ],
    };
  },
  methods: {
    selectQuality(quality) {
      this.$emit('update:modelValue', quality);
    },
  },
}
</script>
