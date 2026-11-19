(() => {
    const closestElement = (target, selector) => {
        return target instanceof Element ? target.closest(selector) : null
    }

    const activateDepartment = (link) => {
        const menu = link.closest('[data-sm-category-menu]')
        if (!menu) return

        const departmentId = link.dataset.smDepartment
        menu.querySelectorAll('[data-sm-department]').forEach((item) => {
            const isActive = item === link
            item.classList.toggle('is-active', isActive)
            if (isActive) item.setAttribute('aria-current', 'true')
            else item.removeAttribute('aria-current')
        })

        menu.querySelectorAll('[data-sm-panel]').forEach((panel) => {
            const isActive = panel.dataset.smPanel === departmentId
            panel.hidden = !isActive
            panel.classList.toggle('is-active', isActive)
        })
    }

    const fitMenuToViewport = (container) => {
        const content = container.querySelector('.menu__content')
        const layout = container.querySelector('.sm-category-menu')
        if (!content || !layout) return

        const top = content.getBoundingClientRect().top
        const availableHeight = Math.max(320, window.innerHeight - top - 16)
        layout.style.setProperty('--sm-category-menu-height', `${availableHeight}px`)
    }

    document.addEventListener('mouseover', (event) => {
        const link = closestElement(event.target, '[data-sm-department]')
        if (link) activateDepartment(link)
    })

    document.addEventListener('focusin', (event) => {
        const link = closestElement(event.target, '[data-sm-department]')
        if (link) activateDepartment(link)
    })

    document.addEventListener('error', (event) => {
        const image = event.target
        if (!(image instanceof HTMLImageElement) || !image.matches('.sm-category-menu__art, .sm-category-menu__item-art, .sm-category-menu__leaf-art')) return

        image.hidden = true
        const fallback = image.nextElementSibling
        if (fallback) fallback.hidden = false
    }, true)

    document.addEventListener('pointerenter', (event) => {
        const container = closestElement(event.target, '.menu--product-categories')
        if (container) fitMenuToViewport(container)
    }, true)

    window.addEventListener('resize', () => {
        document.querySelectorAll('.menu--product-categories').forEach(fitMenuToViewport)
    })

    document.addEventListener('ecommerce.categories-dropdown.success', () => {
        document.querySelectorAll('.menu--product-categories').forEach(fitMenuToViewport)
    })
})()
