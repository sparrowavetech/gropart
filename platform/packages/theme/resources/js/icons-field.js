$(() => {
    'use strict'

    const initIconsField = () => {
        const icons = window.themeIcons || []

        if (!icons) {
            return
        }

        $(document)
            .find('.icon-select')
            .each(function (index, element) {
                const $this = $(element)
                if ($this.data('check-initialized') && $this.hasClass('select2-hidden-accessible')) {
                    return
                }

                let value = $this.children('option:selected').val()

                let options = '<option value="">' + $this.data('empty-value') + '</option>'

                icons.forEach(function (value) {
                    options += '<option value="' + value + '">' + value + '</option>'
                })

                $this.html(options)
                $this.val(value)

                const templateCallback = (state) => {
                    if (!state.id) {
                        return state.text
                    }

                    return $(`<span><i class="${state.id}"></i></span> ${state.text}</span>`)
                }

                Botble.select(element, {
                    templateResult: (state) => templateCallback(state),
                    templateSelection: (state) => templateCallback(state),
                })
            })
    }

    initIconsField()

    document.addEventListener('core-init-resources', function () {
        initIconsField()
    })
})
