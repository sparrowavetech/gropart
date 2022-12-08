import Vue from 'vue';
import sanitizeHTML from 'sanitize-html';
import _ from 'lodash';
import emitter from 'tiny-emitter/instance';

Vue.prototype.__ = key => {
    return _.get(window.trans, key, key);
};

Vue.prototype.$sanitize = sanitizeHTML;

class VueApp {
    constructor() {
        this.vue = Vue;
        this.bootingCallbacks = [];
        this.bootedCallbacks = [];
        this.vueInstance = null;
        this.eventBus = {
            $on: (...args) => emitter.on(...args),
            $once: (...args) => emitter.once(...args),
            $off: (...args) => emitter.off(...args),
            $emit: (...args) => emitter.emit(...args)
        };
    }

    booting(callback) {
        this.bootingCallbacks.push(callback);
    }

    booted(callback) {
        this.bootedCallbacks.push(callback);
    }

    boot() {
        for (const callback of this.bootingCallbacks) {
            callback(this.vue);
        }

        this.vueInstance = new this.vue({
            el: '#app'
        });

        for (const callback of this.bootedCallbacks) {
            callback(this);
        }
    }
}

window.vueApp = new VueApp();
