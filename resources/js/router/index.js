import { createRouter, createWebHistory } from 'vue-router';
import SingleDownload from '../views/SingleDownload.vue';
import QueueStatus from '../views/QueueStatus.vue';
import PlaylistImport from '../views/PlaylistImport.vue';
import DownloadHistory from '../views/DownloadHistory.vue';
import Dashboard from '../views/Dashboard.vue';
import Settings from '../views/Settings.vue';

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
  },
  {
    path: '/download',
    name: 'SingleDownload',
    component: SingleDownload,
  },
  {
    path: '/history',
    name: 'DownloadHistory',
    component: DownloadHistory,
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
  },
  {
    path: '/playlist',
    name: 'PlaylistImport',
    component: PlaylistImport,
  },
  {
    path: '/queue',
    name: 'QueueStatus',
    component: QueueStatus,
  },
];


const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
