(()=>{function t(e){return t="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},t(e)}function e(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,o(r.key),r)}}function o(e){var o=function(e,o){if("object"!=t(e)||!e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var r=n.call(e,o||"default");if("object"!=t(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===o?String:Number)(e)}(e,"string");return"symbol"==t(o)?o:String(o)}var n=function(){function t(){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t)}var o,n,r;return o=t,(n=[{key:"init",value:function(){$(document).on("click",".btn-trigger-active-theme",(function(t){t.preventDefault();var e=$(t.currentTarget);Botble.showButtonLoading(e),$httpClient.make().post(route("theme.active",{theme:e.data("theme")})).then((function(t){var e=t.data;Botble.showSuccess(e.message),window.location.reload()})).finally((function(){Botble.hideButtonLoading(e)}))})),$(document).on("click",".btn-trigger-remove-theme",(function(t){t.preventDefault(),$("#confirm-remove-theme-button").data("theme",$(t.currentTarget).data("theme")),$("#remove-theme-modal").modal("show")})),$(document).on("click","#confirm-remove-theme-button",(function(t){t.preventDefault();var e=$(t.currentTarget);Botble.showButtonLoading(e),$httpClient.make().post(route("theme.remove",{theme:e.data("theme")})).then((function(t){var e=t.data;Botble.showSuccess(e.message),window.location.reload()})).finally((function(){Botble.hideButtonLoading(e),$("#remove-theme-modal").modal("hide")}))}))}}])&&e(o.prototype,n),r&&e(o,r),Object.defineProperty(o,"prototype",{writable:!1}),t}();$((function(){(new n).init()}))})();