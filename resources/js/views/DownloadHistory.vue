<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white mb-2">History</h1>
        <p class="text-gray-400">View and manage your completed downloads</p>
      </div>
      
      <div class="flex items-center space-x-3">
         <button 
           @click="clearHistory"
           class="px-4 py-2 border border-red-800 text-red-500 hover:bg-red-900/20 rounded-lg text-sm transition-colors"
         >
           Clear History
         </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-lg p-4 mb-6 border border-gray-700 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="relative w-full md:w-96">
        <input 
          v-model="filters.search"
          type="text" 
          placeholder="Search items..."
          class="w-full bg-gray-900 border border-gray-600 rounded-lg pl-10 pr-4 py-2 text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"
          @input="debounceSearch"
        />
        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
      </div>
      
      <div class="flex items-center space-x-4 w-full md:w-auto">
        <select 
          v-model="filters.format" 
          class="bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-purple-500 outline-none"
          @change="fetchHistory"
        >
          <option value="">All Formats</option>
          <option value="mp3">MP3</option>
          <option value="flac">FLAC</option>
        </select>
        
        <select 
          v-model="filters.sort" 
          class="bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-purple-500 outline-none"
          @change="fetchHistory"
        >
          <option value="completed_at">Date Desc</option>
          <option value="title">Title Asc</option>
          <option value="file_size">Size Desc</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div v-if="loading" class="py-12 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto"></div>
    </div>
    <HistoryTable v-else :downloads="downloads" />
    
    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page" class="mt-6 flex justify-center space-x-2">
       <button 
         @click="changePage(pagination.current_page - 1)"
         :disabled="pagination.current_page === 1"
         class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white disabled:opacity-50"
       >
         Previous
       </button>
       <span class="px-4 py-2 text-gray-400">
         Page {{ pagination.current_page }} of {{ pagination.last_page }}
       </span>
       <button 
         @click="changePage(pagination.current_page + 1)"
         :disabled="pagination.current_page === pagination.last_page"
         class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white disabled:opacity-50"
       >
         Next
       </button>
    </div>

  </div>
</template>

<script>
import { historyAPI } from '../services/api';
import HistoryTable from '../components/HistoryTable.vue';

export default {
  name: 'DownloadHistory',
  components: {
    HistoryTable
  },
  data() {
    return {
      downloads: [],
      loading: true,
      filters: {
        search: '',
        format: '',
        sort: 'completed_at'
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0
      },
      searchTimeout: null
    };
  },
  mounted() {
    this.fetchHistory();
  },
  methods: {
    async fetchHistory(page = 1) {
      this.loading = true;
      try {
        const params = {
          page,
          search: this.filters.search,
          format: this.filters.format,
          sort_by: this.filters.sort,
          per_page: 20
        };
        
        const response = await historyAPI.getAll(params);
        this.downloads = response.data.data;
        this.pagination = {
           current_page: response.data.current_page,
           last_page: response.data.last_page,
           per_page: response.data.per_page,
           total: response.data.total
        };
      } catch (error) {
        console.error('Failed to fetch history', error);
      } finally {
        this.loading = false;
      }
    },
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.fetchHistory(page);
      }
    },
    debounceSearch() {
      if (this.searchTimeout) clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.fetchHistory(1);
      }, 500);
    },
    async clearHistory() {
      if (!confirm('Are you sure you want to clear your download history? Files will be kept.')) return;
      
      try {
        await historyAPI.clear(true);
        this.fetchHistory();
      } catch (error) {
        alert('Failed to clear history');
      }
    }
  }
}
</script>
