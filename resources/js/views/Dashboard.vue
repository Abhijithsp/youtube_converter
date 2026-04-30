<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-white mb-2">Dashboard</h1>
      <p class="text-gray-400">Overview of your activity and server status</p>
    </div>

    <!-- Stats Grid -->
    <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto"></div>
    </div>
    
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Downloads -->
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg relative overflow-hidden group">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-3 -translate-y-3 group-hover:scale-110 transition-transform">
           <svg class="w-24 h-24 text-purple-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path></svg>
        </div>
        <div class="relative z-10">
          <div class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-1">Total Downloads</div>
          <div class="text-3xl font-bold text-white">{{ stats.total_downloads }}</div>
        </div>
      </div>

      <!-- Storage Used -->
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg relative overflow-hidden group">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-3 -translate-y-3 group-hover:scale-110 transition-transform">
           <svg class="w-24 h-24 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
        </div>
        <div class="relative z-10">
          <div class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-1">Storage Used</div>
          <div class="text-3xl font-bold text-blue-400">{{ stats.total_storage }}</div>
          <div class="text-xs text-gray-500 mt-2">Free: {{ stats.disk_space_free }}</div>
        </div>
      </div>
      
       <!-- Queue Status -->
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg relative overflow-hidden group">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-3 -translate-y-3 group-hover:scale-110 transition-transform">
           <svg class="w-24 h-24 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
        </div>
        <div class="relative z-10">
          <div class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-1">Queue Pending</div>
          <div class="text-3xl font-bold text-yellow-400">{{ stats.queue_pending }}</div>
          <div class="text-xs text-gray-500 mt-2">
             <router-link to="/queue" class="hover:text-white underline">View Queue</router-link>
          </div>
        </div>
      </div>
      
       <!-- Failed Jobs -->
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg relative overflow-hidden group">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-3 -translate-y-3 group-hover:scale-110 transition-transform">
           <svg class="w-24 h-24 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
        </div>
        <div class="relative z-10">
          <div class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-1">Failed Jobs</div>
          <div class="text-3xl font-bold text-red-400">{{ stats.jobs_failed }}</div>
          <div v-if="stats.jobs_failed > 0" class="text-xs text-red-500 mt-2">
             <router-link to="/queue" class="hover:text-red-300 underline">Resolve Issues</router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div v-if="!loading" class="bg-gray-800 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-white">Recent Downloads</h3>
        <router-link to="/history" class="text-sm text-purple-400 hover:text-purple-300">View All</router-link>
      </div>
      <div>
         <div v-if="!stats.recent_downloads || stats.recent_downloads.length === 0" class="p-8 text-center text-gray-500">
           No recent activity
         </div>
         <table v-else class="w-full text-left text-sm text-gray-400">
            <tbody class="divide-y divide-gray-700">
              <tr v-for="download in stats.recent_downloads" :key="download.id" class="hover:bg-gray-700/30">
                <td class="px-6 py-3">
                   <div class="text-white font-medium truncate max-w-xs">{{ download.title }}</div>
                </td>
                <td class="px-6 py-3 text-right text-xs">
                   {{ new Date(download.completed_at).toLocaleDateString() }}
                </td>
              </tr>
            </tbody>
         </table>
      </div>
    </div>

  </div>
</template>

<script>
import { dashboardAPI } from '../services/api';

export default {
  name: 'Dashboard',
  data() {
    return {
      stats: {},
      loading: true
    };
  },
  mounted() {
    this.fetchStats();
  },
  methods: {
    async fetchStats() {
      try {
        const response = await dashboardAPI.getStats();
        this.stats = response.data;
      } catch (error) {
        console.error('Failed to fetch dashboard stats', error);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>
