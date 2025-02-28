import Vue from 'vue';
import { translate, translatePlural } from '@nextcloud/l10n';
import App from './App';
import AppAdmin from './AppAdmin';
import './fileActions';
import config from './config';

Vue.prototype.$t = translate;
Vue.prototype.$n = translatePlural;
Vue.prototype.$globalConfig = config;

let vueInstance;
if (document.getElementById('electronic-signatures-root')) {
  vueInstance = new Vue({
    el: '#electronic-signatures-root',
    render: h => h(App),
  });
}

const adminRootElement = document.getElementById('electronic-signatures-admin-root');
if (adminRootElement) {
  vueInstance = new Vue({
    el: adminRootElement,
    data: () => Object.assign({}, adminRootElement.dataset),
    render: h => h(AppAdmin),
  });
}

export default vueInstance;
