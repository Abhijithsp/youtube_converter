<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-white mb-2">Queue Status</h1>
      <p class="text-gray-400">Monitor and manage background download tasks</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-2">Pending</div>
        <div class="text-3xl font-bold text-white">{{ stats.pending }}</div>
      </div>
      <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-2">Processing</div>
        <div class="text-3xl font-bold text-blue-400">{{ stats.processing }}</div>
      </div>
      <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-2">Failed</div>
        <div class="text-3xl font-bold text-red-400">{{ stats.failed }}</div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex space-x-1 bg-gray-800/50 p-1 rounded-lg mb-6 w-fit">
      <button 
        @click="activeTab = 'active'"
        class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
        :class="activeTab === 'active' ? 'bg-purple-600 text-white shadow' : 'text-gray-400 hover:text-white'"
      >
        Active Jobs
      </button>
      <button 
        @click="activeTab = 'failed'"
        class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
        :class="activeTab === 'failed' ? 'bg-purple-600 text-white shadow' : 'text-gray-400 hover:text-white'"
      >
        Failed Jobs
      </button>
    </div>

    <!-- Active Jobs List -->
    <div v-if="activeTab === 'active'" class="space-y-4">
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto"></div>
        <p class="text-gray-400 mt-4">Loading queue...</p>
      </div>
      <div v-else-if="jobs.length === 0" class="bg-gray-800/30 rounded-lg p-12 text-center border border-gray-700 border-dashed">
        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p class="text-xl text-gray-400">Queue is empty</p>
        <p class="text-sm text-gray-500 mt-2">New downloads will appear here</p>
      </div>
      <div v-else>
        <QueueItem 
          v-for="job in jobs" 
          :key="job.id" 
          :job="job" 
          @cancel="cancelJob"
        />
      </div>
    </div>

    <!-- Failed Jobs List -->
    <div v-if="activeTab === 'failed'" class="space-y-4">
      <div v-if="failedJobs.length > 0" class="flex justify-end space-x-3 mb-4">
        <button 
          @click="retryAll"
          class="flex items-center space-x-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          <span>Retry All</span>
        </button>
        <button 
          @click="clearFailed"
          class="flex items-center space-x-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          <span>Clear List</span>
        </button>
      </div>

      <div v-if="loadingFailed" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500 mx-auto"></div>
        <p class="text-gray-400 mt-4">Loading failed jobs...</p>
      </div>
      <div v-else-if="failedJobs.length === 0" class="bg-gray-800/30 rounded-lg p-12 text-center border border-gray-700 border-dashed">
        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-xl text-gray-400">No failed jobs</p>
        <p class="text-sm text-gray-500 mt-2">Everything is running smoothly</p>
      </div>
      <div v-else>
        <QueueItem 
          v-for="job in failedJobs" 
          :key="job.id" 
          :job="job" 
          :isFailedList="true"
          @retry="retryJob"
        />
      </div>
    </div>
    
    <!-- Toast/Notification placeholder (could be a component) -->
    <div v-if="toast.show" class="fixed bottom-4 right-4 p-4 rounded-lg shadow-lg text-white transition-opacity duration-300" :class="toast.type === 'error' ? 'bg-red-600' : 'bg-green-600'">
      {{ toast.message }}
    </div>
  </div>
</template>

<script>
import { queueAPI } from '../services/api';
import QueueItem from '../components/QueueItem.vue';

export default {
  name: 'QueueStatus',
  components: {
    QueueItem
  },
  data() {
    return {
      activeTab: 'active',
      stats: {
        pending: 0,
        processing: 0,
        failed: 0
      },
      jobs: [],
      failedJobs: [],
      loading: false,
      loadingFailed: false,
      pollInterval: null,
      toast: {
        show: false,
        message: '',
        type: 'success'
      }
    };
  },
  mounted() {
    this.refreshAll();
    // Poll every 3 seconds
    this.pollInterval = setInterval(this.refreshAll, 3000);
  },
  beforeUnmount() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
  },
  methods: {
    async refreshAll() {
      await Promise.all([
        this.fetchStats(),
        this.activeTab === 'active' ? this.fetchJobs() : this.fetchFailedJobs()
      ]);
    },
    async fetchStats() {
      try {
        const response = await queueAPI.getStatus();
        this.stats = response.data;
      } catch (error) {
        console.error('Failed to fetch queue stats', error);
      }
    },
    async fetchJobs() {
      // Don't set loading on poll updates to avoid flickering
      // this.loading = true; 
      try {
        const response = await queueAPI.getJobs();
        this.jobs = response.data;
      } catch (error) {
        console.error('Failed to fetch jobs', error);
      } finally {
        // this.loading = false;
      }
    },
    async fetchFailedJobs() {
      try {
        const response = await queueAPI.getFailedJobs();
        this.failedJobs = response.data;
      } catch (error) {
        console.error('Failed to fetch failed jobs', error);
      }
    },
    async cancelJob(id) {
      try {
        await queueAPI.cancelJob(id);
        this.showToast('Job cancelled successfully');
        this.refreshAll();
      } catch (error) {
        this.showToast('Failed to cancel job', 'error');
      }
    },
    async retryJob(id) {
      try {
        await queueAPI.retryJob(id);
        this.showToast('Job queued for retry');
        this.refreshAll();
      } catch (error) {
        this.showToast('Failed to retry job', 'error');
      }
    },
    async retryAll() {
      try {
        await queueAPI.retryAllFailed();
        this.showToast('All failed jobs queued for retry');
        this.refreshAll();
      } catch (error) {
        this.showToast('Failed to retry all jobs', 'error');
      }
    },
    async clearFailed() {
      if (!confirm('Are you sure you want to clear all failed jobs history?')) return;
      try {
        await queueAPI.clearFailed();
        this.showToast('Failed jobs cleared');
        this.refreshAll();
      } catch (error) {
        this.showToast('Failed to clear jobs', 'error');
      }
    },
    showToast(message, type = 'success') {
      this.toast = { show: true, message, type };
      setTimeout(() => {
        this.toast.show = false;
      }, 3000);
    }
  },
  watch: {
    activeTab() {
      this.refreshAll();
    }
  }
}
</script>
