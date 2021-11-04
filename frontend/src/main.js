import Vue from 'vue'
import './plugins/axios'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import store from './store'
import VueI18n from 'vue-i18n'
import storage from './helpers/storage.js'
import vuetify from '@/plugins/vuetify'

Vue.use(VueI18n)
const axios = require('axios').default;

import en from '../src/components/languages/en'
import ru from '../src/components/languages/ru'
let locale = 'en'
if (store.state.locale != '') {
  locale = store.state.locale
} else {
  store.dispatch('setLocale', locale)
}

const i18n = new VueI18n({
  locale: locale,
  silentTranslationWarn: true,
  messages: {
    'en': en,
    'ru': ru,
  },
});

// console.log(storage.state.abc);
Vue.prototype.storage = storage

Vue.config.productionTip = false
new Vue({
  router,
  store,
  vuetify,
  i18n,
  axios,
  render: h => h(App)
}).$mount('#app')
