<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-white mb-2">Import Playlist</h1>
      <p class="text-gray-400">Download multiple videos or entire albums</p>
    </div>

    <!-- Step 1: Input URL -->
    <div v-if="step === 1" class="bg-gray-800 rounded-xl p-8 border border-gray-700 shadow-xl">
      <form @submit.prevent="fetchPlaylist" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Playlist URL</label>
          <div class="relative">
            <input
              v-model="url"
              type="text"
              placeholder="https://www.youtube.com/playlist?list=..."
              class="w-full bg-gray-900 border border-gray-600 rounded-lg py-3 px-4 text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all"
              required
            />
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:from-purple-700 hover:to-indigo-700 transform hover:scale-[1.02] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-else>Fetch Playlist</span>
        </button>

        <ErrorAlert v-if="error" :message="error" class="mt-4" />
      </form>
    </div>

    <!-- Step 2: Select Videos -->
    <div v-else-if="step === 2" class="space-y-6">
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-xl font-bold text-white">{{ playlistInfo.title }}</h2>
            <p class="text-gray-400 text-sm mt-1">{{ selectedCount }} videos selected</p>
          </div>
          <button 
            @click="step = 1"
            class="text-gray-400 hover:text-white text-sm"
          >
            Change URL
          </button>
        </div>

        <!-- Format Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <FormatSelector v-model="format" />
          <QualitySelector v-model="quality" />
        </div>

        <!-- Videos Table -->
        <div class="overflow-hidden rounded-lg border border-gray-700">
          <div class="max-h-96 overflow-y-auto custom-scrollbar">
            <table class="w-full text-left text-sm text-gray-400">
              <thead class="bg-gray-900 text-gray-200 sticky top-0">
                <tr>
                  <th class="px-4 py-3 w-10">
                    <input 
                      type="checkbox" 
                      :checked="allSelected" 
                      @change="toggleSelectAll"
                      class="rounded bg-gray-700 border-gray-600 text-purple-600 focus:ring-purple-500" 
                    />
                  </th>
                  <th class="px-4 py-3">#</th>
                  <th class="px-4 py-3">Title</th>
                  <th class="px-4 py-3 text-right">Duration</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-700 bg-gray-800">
                <tr 
                  v-for="(video, index) in playlistInfo.videos" 
                  :key="video.id"
                  class="hover:bg-gray-700/50 transition-colors"
                  :class="{ 'bg-purple-900/10': video.selected }"
                >
                  <td class="px-4 py-3">
                    <input 
                      type="checkbox" 
                      v-model="video.selected" 
                      class="rounded bg-gray-700 border-gray-600 text-purple-600 focus:ring-purple-500" 
                    />
                  </td>
                  <td class="px-4 py-3">{{ index + 1 }}</td>
                  <td class="px-4 py-3 font-medium text-white truncate max-w-sm">
                    {{ video.title }}
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-xs">
                    {{ formatDuration(video.duration) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="importPlaylist"
            :disabled="loading || selectedCount === 0"
            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-3 px-8 rounded-lg hover:from-purple-700 hover:to-indigo-700 transform hover:scale-[1.02] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-else>Import {{ selectedCount }} Videos</span>
          </button>
        </div>
        
        <ErrorAlert v-if="error" :message="error" class="mt-4" />
      </div>
    </div>
  </div>
</template>

<script>
import { playlistAPI } from '../services/api';
import FormatSelector from '../components/FormatSelector.vue';
import QualitySelector from '../components/QualitySelector.vue';
import ErrorAlert from '../components/ErrorAlert.vue';

export default {
  name: 'PlaylistImport',
  components: {
    FormatSelector,
    QualitySelector,
    ErrorAlert,
  },
  data() {
    return {
      step: 1,
      url: '',
      loading: false,
      error: null,
      playlistInfo: null,
      format: 'mp3',
      quality: '320',
    };
  },
  computed: {
    selectedCount() {
      if (!this.playlistInfo) return 0;
      return this.playlistInfo.videos.filter(v => v.selected).length;
    },
    allSelected() {
      if (!this.playlistInfo || this.playlistInfo.videos.length === 0) return false;
      return this.playlistInfo.videos.every(v => v.selected);
    }
  },
  methods: {
    async fetchPlaylist() {
      this.loading = true;
      this.error = null;
      try {
        const response = await playlistAPI.fetchInfo(this.url);
        this.playlistInfo = response.data;
        // Add selected property to all videos
        this.playlistInfo.videos = this.playlistInfo.videos.map(v => ({ ...v, selected: true }));
        this.step = 2;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch playlist info';
      } finally {
        this.loading = false;
      }
    },
    toggleSelectAll() {
      const newState = !this.allSelected;
      this.playlistInfo.videos.forEach(v => v.selected = newState);
    },
    async importPlaylist() {
      this.loading = true;
      this.error = null;
      
      const selectedVideos = this.playlistInfo.videos.filter(v => v.selected);
      
      try {
        await playlistAPI.create({
          url: this.url,
          title: this.playlistInfo.title,
          items: selectedVideos,
          format: this.format,
          quality: parseInt(this.quality)
        });
        
        // Redirect to queue
        this.$router.push('/queue');
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to import playlist';
      } finally {
        this.loading = false;
      }
    },
    formatDuration(seconds) {
      if (!seconds) return '--:--';
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #1f2937; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #4b5563; 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #6b7280; 
}
</style>
