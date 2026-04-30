<template>
  <div class="bg-gray-800/50 rounded-lg p-4 mb-3 border border-gray-700 hover:border-gray-600 transition-colors">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <!-- Icon based on status -->
        <div class="p-2 rounded-full" :class="statusColorClass">
          <svg v-if="isProcessing" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else-if="isFailed" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>

        <div>
          <h4 class="text-white font-medium">
            {{ jobTitle }}
          </h4>
          <div class="text-sm text-gray-400 mt-1 flex items-center space-x-2">
            <span>ID: {{ job.id }}</span>
            <span>•</span>
            <span>{{ statusText }}</span>
            <span v-if="job.attempts > 0">• Attempt {{ job.attempts }}</span>
            <span v-if="job.created_at">• {{ formatDate(job.created_at) }}</span>
          </div>
          <div v-if="errorMessage" class="text-xs text-red-400 mt-1">
            {{ errorMessage }}
          </div>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <button 
          v-if="canRetry"
          @click="$emit('retry', job.id)"
          class="px-3 py-1 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors"
        >
          Retry
        </button>
        <button 
          v-if="canCancel"
          @click="$emit('cancel', job.id)"
          class="px-3 py-1 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded transition-colors"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QueueItem',
  props: {
    job: {
      type: Object,
      required: true
    },
    isFailedList: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    isProcessing() {
      // If it has reserved_at, it's processing (or stuck)
      return !this.isFailedList && this.job.reserved_at;
    },
    isFailed() {
      return this.isFailedList;
    },
    statusColorClass() {
      if (this.isFailed) return 'bg-red-500/20 text-red-400';
      if (this.isProcessing) return 'bg-blue-500/20 text-blue-400';
      return 'bg-gray-700 text-gray-400';
    },
    statusText() {
      if (this.isFailed) return 'Failed';
      if (this.isProcessing) return 'Processing';
      return 'Pending';
    },
    jobTitle() {
      if (this.job.download && this.job.download.title) {
        return this.job.download.title;
      }
      return 'Download Task';
    },
    canRetry() {
      return this.isFailedList;
    },
    canCancel() {
      return !this.isFailedList;
    },
    errorMessage() {
      if (!this.job.exception) return null;
      // Extract first line of exception
      return this.job.exception.split('\n')[0].substring(0, 100) + '...';
    }
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr * 1000).toLocaleString(); // timestamp is usually seconds in Laravel jobs table? 
      // Actually Laravel jobs table created_at is strictly integer timestamp.
    }
  }
}
</script>
