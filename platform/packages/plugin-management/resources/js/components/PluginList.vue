<script>
import { defineComponent } from 'vue'
import PluginItem from './PluginItem.vue'
import PluginDetails from './PluginDetails.vue'
import Pagination from './Pagination.vue'
import PluginPlaceholder from "./PluginPlaceholder.vue";

export default defineComponent({
    components: {
        PluginPlaceholder,
        PluginItem,
        PluginDetails,
        Pagination,
    },

    data() {
        return {
            initializing: true,
            loading: true,
            plugins: [],
            meta: {},
            filter: 'all',
            sort: 'default',
            search: window.location.search.replace('?q=', ''),
            page: 1,
            showingPlugin: null,
            lastPluginsCount: 12,
        }
    },

    mounted() {
        this.getPlugins()
    },

    computed: {
        params() {
            const params = {
                page: this.page,
                q: this.search,
            }

            if (this.filter === 'featured') {
                Object.assign(params, { is_featured: true })
            }

            switch (this.sort) {
                case 'popular':
                    Object.assign(params, { is_popular: true })
                    break

                case 'top_rated':
                    Object.assign(params, { is_top_rating: true })
                    break
            }

            return params
        },
    },

    watch: {
        page() {
            this.getPlugins()
        },

        search: _.debounce(function () {
            window.history.replaceState({}, null, this.search === '' ? window.location.pathname : `?q=${this.search}`)

            this.page = 1
            this.getPlugins()
        }, 300),

        filter() {
            this.page = 1
            this.getPlugins()
        },

        sort() {
            this.page = 1
            this.getPlugins()
        },
    },

    methods: {
        getPlugins() {
            this.loading = true
            this.plugins = []

            $httpClient
                .make()
                .get(route('plugins.marketplace.ajax.list'), { ...this.params })
                .then(({ data }) => {
                    this.plugins = data.data
                    this.meta = data.meta
                    this.initializing = false
                    this.lastPluginsCount = this.plugins.length
                })
                .finally(() => (this.loading = false))
        },
        setPage(page) {
            this.page = page
        },
        showPlugin(plugin) {
            this.showingPlugin = plugin
        },
        goBack() {
            this.showingPlugin = null
        },
        install(event, id) {
            const url = window.marketplace.route.install

            $httpClient
                .make()
                .withButtonLoading(event.currentTarget)
                .post(url.replace(':id', id))
                .then(({ data }) => {
                    Botble.showSuccess(data.message)

                    $event.emit('plugin-installed', data.data.name)

                    window.marketplace.installed.push(data.data.name)
                })
        },
        uninstall(event, plugin) {
            if (!confirm(this.__('This action will remove all data of this plugin. Are you sure you want continue?'))) {
                return
            }

            $httpClient
                .make()
                .withButtonLoading(event.currentTarget)
                .delete(route('plugins.remove', { plugin: plugin }))
                .then(({ data }) => {
                    Botble.showSuccess(data.message)

                    $event.emit('plugin-uninstalled', plugin)

                    window.marketplace.installed.splice(window.marketplace.installed.indexOf(plugin), 1)
                })
        },
        toggleActivation(event, name) {
            $httpClient
                .make()
                .withButtonLoading(event.currentTarget)
                .put(window.marketplace.route.active, { name })
                .then(({ data }) => {
                    Botble.showSuccess(data.message)

                    $event.emit('plugin-toggle-activation', name)

                    data.data.status === 'activated'
                        ? window.marketplace.activated.push(name)
                        : window.marketplace.activated.splice(window.marketplace.activated.indexOf(name), 1)
                })
        },
    },
})
</script>

<template>
    <div>
        <div v-if="initializing" class="card">
            <div class="card-body" style="margin: 3rem auto">
                <div class="loading-spinner"></div>
            </div>
        </div>

        <template v-else>
            <div class="card mb-3">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li
                            v-for="(item, index) in ['all', 'featured']"
                            class="nav-item"
                            :key="index"
                            role="presentation"
                        >
                            <button
                                href="javascript:(0)"
                                class="nav-link position-relative"
                                @click="filter = item"
                                :class="{ active: filter === item }"
                                data-bs-toggle="tab"
                                aria-selected="true"
                                role="tab"
                                :disabled="loading"
                            >
                                {{ __(`base.${item}`) }}
                                <span
                                    class="badge bg-blue text-blue-fg badge-notification badge-pill"
                                    v-if="filter === item && !loading"
                                >
                                    {{ meta.total }}
                                </span>
                            </button>
                        </li>
                        <li class="nav-item ms-auto">
                            <div class="dropdown">
                                <a
                                    href="#"
                                    class="nav-link"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="icon me-1icon-tabler-sort-descending"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M4 6l9 0"></path>
                                        <path d="M4 12l7 0"></path>
                                        <path d="M4 18l7 0"></path>
                                        <path d="M15 15l3 3l3 -3"></path>
                                        <path d="M18 6l0 12"></path>
                                    </svg>
                                    {{ __(`base.${sort}`) }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <button
                                        class="dropdown-item"
                                        v-for="(item, index) in ['default', 'popular', 'top_rated']"
                                        :key="index"
                                        :class="{ active: sort === item }"
                                        @click="sort = item"
                                    >
                                        {{ __(`base.${item}`) }}
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-flat">
                        <span class="input-group-text">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="icon"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path>
                                <path d="M21 21l-6 -6"></path>
                            </svg>
                        </span>
                        <input type="text" v-model="search" class="form-control" :placeholder="__('base.search')" />
                        <span class="input-group-text" v-if="search">
                            <a
                                href="javascript:void(0)"
                                class="link-secondary"
                                data-bs-toggle="tooltip"
                                :aria-label="__('base.clear_search')"
                                :title="__('base.clear_search')"
                                @click="search = ''"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="icon"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    fill="none"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M18 6l-12 12"></path>
                                    <path d="M6 6l12 12"></path>
                                </svg>
                            </a>
                        </span>
                    </div>
                </div>
            </div>

            <div class="mb-3 row row-cards position-relative">
                <plugin-placeholder
                    v-if="loading"
                    v-for="(item) in this.lastPluginsCount"
                    :key="item"
                />

                <plugin-item
                    v-for="(plugin, index) in plugins"
                    :key="index"
                    :plugin="plugin"
                    @showPlugin="showPlugin"
                    @install="install"
                    @uninstall="uninstall"
                    @toggle-activation="toggleActivation"
                />
            </div>

            <pagination :meta="meta" @page-selected="setPage" :scroll-to-top="true" v-if="meta.last_page > 1" />

            <plugin-details
                v-if="showingPlugin"
                :plugin="showingPlugin"
                @back="goBack"
                @install="install"
                @uninstall="uninstall"
                @toggle-activation="toggleActivation"
            />
        </template>
    </div>
</template>
