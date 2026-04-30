<template>
  <div class="overflow-x-auto rounded-lg border border-gray-700">
    <table class="w-full text-left text-sm text-gray-400">
      <thead class="bg-gray-900 text-gray-200 uppercase font-medium text-xs">
        <tr>
          <th class="px-6 py-4">Title</th>
          <th class="px-6 py-4">Format</th>
          <th class="px-6 py-4 text-right">Size</th>
          <th class="px-6 py-4 text-right">Date</th>
          <th class="px-6 py-4 text-center">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-700 bg-gray-800">
        <tr 
          v-for="download in downloads" 
          :key="download.id"
          class="hover:bg-gray-700/50 transition-colors"
        >
          <td class="px-6 py-4">
            <div class="flex items-center space-x-3">
              <div v-if="download.thumbnail_url" class="flex-shrink-0 h-10 w-10">
                <img :src="download.thumbnail_url" class="h-10 w-10 rounded object-cover" alt="">
              </div>
              <div class="max-w-xs truncate">
                <div class="text-white font-medium truncate">{{ download.title }}</div>
                <div class="text-gray-500 text-xs">{{ download.artist || 'Unknown Artist' }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4">
            <span class="px-2 py-1 text-xs rounded-full bg-gray-700 text-gray-300 uppercase">
              {{ download.format }} {{ download.quality }}k
            </span>
          </td>
          <td class="px-6 py-4 text-right font-mono text-xs">
            {{ formatBytes(download.file_size) }}
          </td>
          <td class="px-6 py-4 text-right text-xs">
            {{ formatDate(download.completed_at) }}
          </td>
          <td class="px-6 py-4 text-center">
            <div class="flex items-center justify-center space-x-2">
              <a 
                :href="download.download_url || '#'" 
                target="_blank"
                class="p-2 text-blue-400 hover:text-blue-300 transition-colors"
                title="Download"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
              </a>
            </div>
          </td>
        </tr>
        <tr v-if="downloads.length === 0">
          <td colspan="5" class="px-6 py-12 text-center text-gray-500">
            No history found
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'HistoryTable',
  props: {
    downloads: {
      type: Array,
      required: true
    }
  },
  methods: {
    formatBytes(bytes) {
      if (bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleDateString();
    }
  }
}
</script>
