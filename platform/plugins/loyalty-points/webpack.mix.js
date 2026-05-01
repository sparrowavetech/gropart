const mix = require('laravel-mix')
const path = require('path')

const directory = path.basename(path.resolve(__dirname))
const source = `platform/plugins/${directory}`
const dist = `public/vendor/core/plugins/${directory}`

mix.sass(`${source}/resources/sass/loyalty-points.scss`, `${dist}/css`)
    .sass(`${source}/resources/sass/loyalty-admin.scss`, `${dist}/css`)
    .sass(`${source}/resources/sass/loyalty-admin-rtl.scss`, `${dist}/css`)
    .sass(`${source}/resources/sass/license-activation.scss`, `${dist}/css`)
    .sass(`${source}/resources/sass/loyalty-checkout.scss`, `${dist}/css`)

mix.js(`${source}/resources/js/license-activation.js`, `${dist}/js`)
    .js(`${source}/resources/js/loyalty-checkout.js`, `${dist}/js`)
    .js(`${source}/resources/js/member-adjustment.js`, `${dist}/js`)
    .js(`${source}/resources/js/loyalty-product-variation.js`, `${dist}/js`)

if (mix.inProduction()) {
    mix.copy(`${dist}/css/loyalty-points.css`, `${source}/public/css`)
        .copy(`${dist}/css/loyalty-admin.css`, `${source}/public/css`)
        .copy(`${dist}/css/loyalty-admin-rtl.css`, `${source}/public/css`)
        .copy(`${dist}/css/license-activation.css`, `${source}/public/css`)
        .copy(`${dist}/css/loyalty-checkout.css`, `${source}/public/css`)
        .copy(`${dist}/js/license-activation.js`, `${source}/public/js`)
        .copy(`${dist}/js/loyalty-checkout.js`, `${source}/public/js`)
        .copy(`${dist}/js/member-adjustment.js`, `${source}/public/js`)
        .copy(`${dist}/js/loyalty-product-variation.js`, `${source}/public/js`)
}
