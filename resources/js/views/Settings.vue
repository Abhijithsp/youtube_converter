<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-white mb-2">Settings</h1>
      <p class="text-gray-400">Configure application preferences and system checks</p>
    </div>

    <!-- Disk Space Check -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg mb-8">
      <h3 class="text-xl font-bold text-white mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        Disk Usage
      </h3>
      <div v-if="diskLoading" class="animate-pulse flex space-x-4">
        <div class="h-4 bg-gray-700 rounded w-3/4"></div>
      </div>
      <div v-else class="space-y-4">
        <div class="flex items-center justify-between text-sm">
           <span class="text-gray-400">Download Path:</span>
           <span class="text-white font-mono bg-gray-900 px-2 py-1 rounded">{{ diskInfo.path }}</span>
        </div>
        <div>
          <div class="flex justify-between mb-1">
            <span class="text-sm font-medium text-gray-300">Storage Usage</span>
            <span class="text-sm font-medium text-gray-300">{{ usedPercent }}%</span>
          </div>
          <div class="w-full bg-gray-700 rounded-full h-2.5">
            <div 
              class="bg-blue-600 h-2.5 rounded-full" 
              :style="{ width: usedPercent + '%' }"
              :class="{ 'bg-red-500': usedPercent > 90, 'bg-yellow-500': usedPercent > 75 }"
            ></div>
          </div>
          <div class="flex justify-between mt-1 text-xs text-gray-500">
             <span>Used: {{ (diskInfo.total_mb - diskInfo.free_mb).toFixed(2) }} MB</span>
             <span>Free: {{ diskInfo.free_mb }} MB</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Configuration Form -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg mb-8">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-white flex items-center">
          <svg class="w-6 h-6 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
          Configuration
        </h3>
        <button 
          @click="saveSettings" 
          :disabled="saving"
          class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-bold transition-colors disabled:opacity-50"
        >
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>

      <div class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Download Path (Absolute Path)</label>
          <input 
            v-model="settings.download_path" 
            type="text" 
            class="w-full bg-gray-900 border border-gray-600 rounded-lg py-2 px-4 text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 outline-none"
          />
          <p class="text-xs text-gray-500 mt-1">Directory where files will be saved.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Max Playlist Size</label>
            <input 
              v-model.number="settings.max_playlist_size" 
              type="number" 
              min="1"
              class="w-full bg-gray-900 border border-gray-600 rounded-lg py-2 px-4 text-white focus:ring-2 focus:ring-purple-500 outline-none"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Max Concurrent Downloads</label>
            <input 
              v-model.number="settings.max_concurrent_downloads" 
              type="number" 
              min="1" max="10"
              class="w-full bg-gray-900 border border-gray-600 rounded-lg py-2 px-4 text-white focus:ring-2 focus:ring-purple-500 outline-none"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Min Disk Space (MB)</label>
            <input 
              v-model.number="settings.min_disk_space_mb" 
              type="number" 
              min="100"
              class="w-full bg-gray-900 border border-gray-600 rounded-lg py-2 px-4 text-white focus:ring-2 focus:ring-purple-500 outline-none"
            />
             <p class="text-xs text-gray-500 mt-1">Downloads will pause if free space is below this.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- System Dependencies -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-white flex items-center">
          <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          System Checks
        </h3>
        <button 
           @click="testDependencies" 
           :disabled="testing"
           class="text-sm text-purple-400 hover:text-white"
        >
           {{ testing ? 'Checking...' : 'Run Checks' }}
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
         <!-- yt-dlp -->
         <div class="bg-gray-900 p-4 rounded-lg flex items-center justify-between">
           <div>
             <div class="text-white font-bold">yt-dlp</div>
             <div class="text-xs text-gray-500">Core downloader</div>
           </div>
           <div class="text-right">
             <div v-if="testing" class="text-gray-500">...</div>
             <div v-else>
                <div v-if="dependencies.yt_dlp && dependencies.yt_dlp.installed" class="text-green-400 text-sm font-bold flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  Installed
                </div>
                <div v-else class="text-red-400 text-sm font-bold flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                  Missing
                </div>
                <!-- <div class="text-xs text-gray-500">{{ dependencies.yt_dlp?.version }}</div> -->
             </div>
           </div>
         </div>

         <!-- ffmpeg -->
         <div class="bg-gray-900 p-4 rounded-lg flex items-center justify-between">
           <div>
             <div class="text-white font-bold">FFmpeg</div>
             <div class="text-xs text-gray-500">Audio converter</div>
           </div>
           <div class="text-right">
             <div v-if="testing" class="text-gray-500">...</div>
             <div v-else>
                <div v-if="dependencies.ffmpeg && dependencies.ffmpeg.installed" class="text-green-400 text-sm font-bold flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  Installed
                </div>
                <div v-else class="text-red-400 text-sm font-bold flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                  Missing
                </div>
                <!-- <div class="text-xs text-gray-500">{{ dependencies.ffmpeg?.version }}</div> -->
             </div>
           </div>
         </div>
      </div>
    </div>
    
     <!-- Toast -->
    <div v-if="toast.show" class="fixed bottom-4 right-4 p-4 rounded-lg shadow-lg text-white transition-opacity duration-300 bg-green-600">
      {{ toast.message }}
    </div>
  </div>
</template>

<script>
import { settingsAPI } from '../services/api';

export default {
  name: 'Settings',
  data() {
    return {
      settings: {
        download_path: '',
        max_playlist_size: 50,
        max_concurrent_downloads: 3,
        min_disk_space_mb: 500
      },
      diskInfo: {
        free_mb: 0,
        total_mb: 0,
        path: ''
      },
      dependencies: {
        yt_dlp: null,
        ffmpeg: null
      },
      diskLoading: true,
      saving: false,
      testing: false,
      toast: { show: false, message: '' }
    }
  },
  computed: {
    usedPercent() {
      if (this.diskInfo.total_mb === 0) return 0;
      const used = this.diskInfo.total_mb - this.diskInfo.free_mb;
      return Math.round((used / this.diskInfo.total_mb) * 100);
    }
  },
  mounted() {
    this.loadSettings();
    this.checkDisk();
    this.testDependencies();
  },
  methods: {
    async loadSettings() {
      try {
        const response = await settingsAPI.get();
        // Ensure numbers are numbers
        this.settings = {
           ...response.data,
           max_playlist_size: parseInt(response.data.max_playlist_size),
           max_concurrent_downloads: parseInt(response.data.max_concurrent_downloads),
           min_disk_space_mb: parseInt(response.data.min_disk_space_mb),
        };
      } catch (e) {
        console.error('Failed to load settings', e);
      }
    },
    async saveSettings() {
      this.saving = true;
      try {
        await settingsAPI.update(this.settings);
        this.showToast('Settings saved successfully');
        // Refresh disk info as path might have changed
        this.checkDisk(); 
      } catch (e) {
        alert('Failed to save settings: ' + (e.response?.data?.message || e.message));
      } finally {
        this.saving = false;
      }
    },
    async checkDisk() {
      this.diskLoading = true;
      try {
        const response = await settingsAPI.checkDiskSpace();
        this.diskInfo = response.data;
      } catch (e) {
        console.error('Failed to check disk space', e);
      } finally {
        this.diskLoading = false;
      }
    },
    async testDependencies() {
      this.testing = true;
      try {
        const response = await settingsAPI.testDependencies();
        this.dependencies = response.data;
      } catch (e) {
         console.error('Failed to test dependencies', e);
      } finally {
        this.testing = false;
      }
    },
    showToast(msg) {
      this.toast = { show: true, message: msg };
      setTimeout(() => this.toast.show = false, 3000);
    }
  }
}
</script>
