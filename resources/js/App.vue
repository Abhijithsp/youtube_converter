<template>
  <div id="app" class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900">
    <nav class="bg-black/30 backdrop-blur-lg border-b border-white/10">
      <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            </svg>
            <h1 class="text-2xl font-bold text-white">YouTube to MP3</h1>
          </div>
          <div class="flex space-x-2">
            <router-link 
              to="/" 
              class="px-3 py-2 rounded-lg text-white hover:bg-white/10 transition-all text-sm"
              :class="{ 'bg-purple-600': $route.path === '/' }"
            >
              Dashboard
            </router-link>
            <router-link 
              to="/download" 
              class="px-3 py-2 rounded-lg text-white hover:bg-white/10 transition-all text-sm"
              :class="{ 'bg-purple-600': $route.path === '/download' }"
            >
              Download
            </router-link>
            <router-link 
              to="/playlist" 
              class="px-3 py-2 rounded-lg text-white hover:bg-white/10 transition-all text-sm"
              :class="{ 'bg-purple-600': $route.path === '/playlist' }"
            >
              Playlist
            </router-link>
             <router-link 
              to="/queue" 
              class="px-3 py-2 rounded-lg text-white hover:bg-white/10 transition-all text-sm"
              :class="{ 'bg-purple-600': $route.path === '/queue' }"
            >
              Queue
            </router-link>
            <router-link 
              to="/history" 
              class="px-3 py-2 rounded-lg text-white hover:bg-white/10 transition-all text-sm"
              :class="{ 'bg-purple-600': $route.path === '/history' }"
            >
              History
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
      <router-view></router-view>
    </main>

    <footer class="mt-16 py-6 text-center text-gray-400 text-sm">
      <p>YouTube to MP3 Converter - Built with Laravel & Vue.js</p>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'App',
}
</script>
