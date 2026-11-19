(() => {
    const dock = document.querySelector('.sm-action-dock')
    if (!dock) return

    const searchButton = dock.querySelector('[data-sm-product-search]')
    const topButton = dock.querySelector('[data-sm-back-to-top]')

    if (searchButton) {
        searchButton.addEventListener('click', () => {
            const search = document.querySelector('.header .input-search-product, .header input[type="search"]')
            if (search) {
                search.scrollIntoView({ behavior: 'smooth', block: 'center' })
                window.setTimeout(() => search.focus(), 250)
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' })
            }
        })
    }

    if (topButton) topButton.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }))
})()
