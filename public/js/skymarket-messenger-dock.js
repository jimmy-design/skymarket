(() => {
    const dock = document.querySelector('.sm-action-dock')
    const panel = document.querySelector('[data-sm-messenger-window]')
    if (!dock) return

    const openButton = dock.querySelector('[data-sm-messenger-open]')
    const loginLink = dock.querySelector('a.sm-action-dock__item')
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
    if (!panel || !openButton) return

    const list = panel.querySelector('[data-sm-messenger-list]')
    const conversation = panel.querySelector('[data-sm-messenger-conversation]')
    const filter = panel.querySelector('[data-sm-messenger-filter]')
    const back = panel.querySelector('[data-sm-messenger-back]')
    let loaded = false
    let conversations = []
    let activeUrl = null

    const setMessage = (message, href) => {
        const paragraph = document.createElement('p')
        paragraph.className = 'sm-messenger-inbox__message'
        paragraph.textContent = message
        list.replaceChildren(paragraph)
        if (href) {
            const link = document.createElement('a')
            link.className = 'sm-messenger-inbox__message'
            link.href = href
            link.textContent = 'Sign in to view your messages'
            list.append(link)
        }
    }

    const openConversation = async (item, threadButton) => {
        if (!item || !item.url) return
        const url = new URL(item.url, window.location.href)
        if (url.origin !== window.location.origin) return
        if (threadButton) {
            list.querySelectorAll('.sm-messenger-thread').forEach((thread) => thread.classList.toggle('is-active', thread === threadButton))
        }
        if (activeUrl === url.href && conversation.querySelector('[data-sm-chat]')) {
            panel.classList.add('is-chat-open')
            return
        }

        activeUrl = url.href
        back.hidden = false
        conversation.innerHTML = '<p class="sm-messenger-conversation__loading">Opening conversation…</p>'
        panel.classList.add('is-chat-open')

        try {
            const response = await fetch(url.href, {
                credentials: 'same-origin',
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            })

            if (response.redirected && new URL(response.url).pathname.includes('login')) {
                activeUrl = null
                conversation.innerHTML = '<div class="sm-messenger-conversation__empty"><strong>Sign in to view this conversation</strong></div>'
                return
            }
            if (!response.ok) throw new Error('This conversation could not be opened.')

            const html = await response.text()
            const parsed = new DOMParser().parseFromString(html, 'text/html')
            const chat = parsed.querySelector('[data-sm-chat]')
            if (!chat) throw new Error('This conversation is unavailable.')

            conversation.replaceChildren(document.importNode(chat, true))
            if (window.SkymarketInitializeMessenger) window.SkymarketInitializeMessenger(conversation)
        } catch (error) {
            activeUrl = null
            const message = document.createElement('p')
            message.className = 'sm-messenger-conversation__loading'
            message.textContent = error.message || 'Unable to connect. Please try again.'
            conversation.replaceChildren(message)
        }
    }

    const renderThreads = (items) => {
        conversations = items
        const query = (filter.value || '').trim().toLocaleLowerCase()
        const matches = items.filter((item) => `${item.supplier} ${item.preview}`.toLocaleLowerCase().includes(query))
        if (!matches.length) {
            setMessage(query ? 'No conversations match your search.' : 'You have no conversations yet.')
            return
        }

        const fragment = document.createDocumentFragment()
        matches.forEach((item) => {
            const thread = document.createElement('button')
            thread.type = 'button'
            thread.className = 'sm-messenger-thread'
            thread.dataset.url = item.url
            const heading = document.createElement('span')
            heading.className = 'sm-messenger-thread__top'
            const name = document.createElement('span')
            name.className = 'sm-messenger-thread__name'
            name.textContent = item.supplier || 'Seller'
            const date = document.createElement('span')
            date.className = 'sm-messenger-thread__date'
            date.textContent = item.updated_at ? new Date(item.updated_at).toLocaleDateString() : ''
            heading.append(name, date)
            const preview = document.createElement('span')
            preview.className = 'sm-messenger-thread__preview'
            preview.textContent = item.preview || ''
            thread.append(heading, preview)
            if (item.unread) {
                const unread = document.createElement('span')
                unread.className = 'sm-messenger-thread__badge'
                unread.textContent = 'New message'
                thread.append(unread)
            }
            thread.addEventListener('click', () => openConversation(item, thread))
            fragment.append(thread)
        })
        list.replaceChildren(fragment)
    }

    const loadInbox = async () => {
        list.innerHTML = '<p class="sm-messenger-inbox__message">Loading conversations…</p>'
        try {
            const response = await fetch(panel.dataset.inboxUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            })
            if (response.redirected) {
                setMessage('Sign in to view your messages.', response.url)
                return
            }
            if (!response.ok) throw new Error('Messages could not be loaded.')
            const data = await response.json()
            if (!Array.isArray(data.conversations)) throw new Error('Messages could not be loaded.')
            loaded = true
            renderThreads(data.conversations)
            if (data.conversations.length && !activeUrl) {
                openConversation(data.conversations[0], list.querySelector('.sm-messenger-thread'))
            }
        } catch (error) {
            setMessage(error.message || 'Messages could not be loaded.')
        }
    }

    const closePanel = () => {
        panel.hidden = true
        panel.classList.remove('is-chat-open')
        openButton.setAttribute('aria-expanded', 'false')
    }

    openButton.addEventListener('click', () => {
        panel.hidden = !panel.hidden
        openButton.setAttribute('aria-expanded', String(!panel.hidden))
        if (!panel.hidden && !loaded) loadInbox()
    })
    panel.querySelector('[data-sm-messenger-close]').addEventListener('click', closePanel)
    back.addEventListener('click', () => {
        panel.classList.remove('is-chat-open')
        back.hidden = true
        conversation.innerHTML = '<div class="sm-messenger-conversation__empty"><strong>Your conversations</strong><span>Choose a seller conversation to read and reply.</span></div>'
    })
    panel.addEventListener('click', (event) => {
        const link = event.target instanceof Element ? event.target.closest('.sm-chat__back') : null
        if (!link) return
        event.preventDefault()
        back.click()
    })
    filter.addEventListener('input', () => renderThreads(conversations))
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) closePanel()
    })
})()
