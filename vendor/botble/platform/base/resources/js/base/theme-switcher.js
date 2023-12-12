const themeStorageKey = 'themeMode'
const defaultTheme = 'light'

const params = new Proxy(new URLSearchParams(window.location.search), {
    /**
     * @param {URLSearchParams} searchParams
     * @param {string} prop
     * @return {string}
     */
    get: function get(searchParams, prop) {
        return searchParams.get(prop)
    },
})

if (!!params.theme) {
    localStorage.setItem(themeStorageKey, params.theme)
    selectedTheme = params.theme
} else {
    const storedTheme = localStorage.getItem(themeStorageKey)
    selectedTheme = storedTheme ? storedTheme : defaultTheme
}

if (! document.body.hasAttribute('data-bs-theme')) {
    if (selectedTheme === 'dark') {
        document.body.setAttribute('data-bs-theme', selectedTheme)
    } else {
        document.body.removeAttribute('data-bs-theme')
    }
}
