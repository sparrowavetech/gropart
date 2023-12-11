import PluginList from './components/PluginList.vue'

if (typeof vueApp !== 'undefined') {
    vueApp.booting((vue) => {
        vue.component('plugin-list', PluginList)
    })
}
