import axios from 'axios';
// window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios = axios;
// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true; // Important pour les cookies

// Configuration de base pour Sanctum
axios.defaults.baseURL = 'http://localhost:8000'; // Ton URL de base