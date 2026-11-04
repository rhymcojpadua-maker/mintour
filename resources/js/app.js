import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

// Toast store shared across pages
Alpine.store('toast', {
    show: false,
    msg: '',
    push(msg) {
        this.msg = msg;
        this.show = true;
        clearTimeout(this._t);
        this._t = setTimeout(() => { this.show = false; }, 2800);
    },
});

// Language store mirrors <html data-lang>
Alpine.store('lang', {
    code: document.documentElement.dataset.lang || 'en',
    set(c) {
        this.code = c;
        document.documentElement.dataset.lang = c;
        document.cookie = `lang=${c};path=/;max-age=31536000`;
        window.dispatchEvent(new CustomEvent('lang-changed', { detail: c }));
    },
});

// Reveal-on-scroll helper
Alpine.directive('reveal', (el, { expression }, { cleanup }) => {
    el.classList.add('reveal');
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                if (expression) el.style.transitionDelay = expression;
                el.classList.add('in');
                io.unobserve(el);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    io.observe(el);
    cleanup(() => io.disconnect());
});

async function loadMapAssets(root) {
    if (root.querySelector('#homeMap, #detailMap, [data-map-features-url]')) {
        await import('./map.js');
    }
}

loadMapAssets(document).then(() => {
    window.Alpine = Alpine;
    Alpine.start();
    document.dispatchEvent(new Event('mintour:map-ready'));
});

history.scrollRestoration = 'manual';
history.replaceState({ ...history.state, scrollY: window.scrollY }, '', window.location.href);

let navigationController = null;
let navigationId = 0;
let scrollSaveTimer = null;

window.addEventListener('scroll', () => {
    window.clearTimeout(scrollSaveTimer);
    scrollSaveTimer = window.setTimeout(() => {
        history.replaceState({ ...history.state, scrollY: window.scrollY }, '', window.location.href);
    }, 100);
}, { passive: true });

function isExecutableScript(script) {
    const type = (script.getAttribute('type') || '').trim().toLowerCase();
    return !type || [
        'module',
        'text/javascript',
        'application/javascript',
        'text/ecmascript',
        'application/ecmascript',
    ].includes(type);
}

async function activatePageScripts(body) {
    for (const inertScript of [...body.querySelectorAll('script')]) {
        if (!isExecutableScript(inertScript)) continue;

        const script = document.createElement('script');
        for (const attribute of inertScript.attributes) {
            script.setAttribute(attribute.name, attribute.value);
        }
        script.textContent = inertScript.textContent;

        if (script.src) {
            script.async = false;
            await new Promise((resolve, reject) => {
                script.addEventListener('load', resolve, { once: true });
                script.addEventListener('error', () => reject(new Error(`Could not load ${script.src}.`)), { once: true });
                inertScript.replaceWith(script);
            });
        } else {
            inertScript.replaceWith(script);
        }
    }
}

function syncDocument(nextDocument) {
    const currentRoot = document.documentElement;
    const nextRoot = nextDocument.documentElement;

    for (const attribute of [...currentRoot.attributes]) {
        if (!nextRoot.hasAttribute(attribute.name)) currentRoot.removeAttribute(attribute.name);
    }
    for (const attribute of nextRoot.attributes) {
        currentRoot.setAttribute(attribute.name, attribute.value);
    }

    document.title = nextDocument.title;
    for (const name of ['description', 'csrf-token']) {
        const nextMeta = nextDocument.head.querySelector(`meta[name="${name}"]`);
        const currentMeta = document.head.querySelector(`meta[name="${name}"]`);
        if (nextMeta && currentMeta) currentMeta.content = nextMeta.content;
    }

    const language = nextRoot.dataset.lang;
    if (language && Alpine.store('lang').code !== language) {
        Alpine.store('lang').code = language;
        window.dispatchEvent(new CustomEvent('lang-changed', { detail: language }));
    }
}

async function renderPage(response, url, { historyMode, scrollY = 0 } = {}) {
    const html = await response.text();
    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
    if (!nextDocument.body) throw new Error('The requested page did not contain a document body.');

    const oldBody = document.body;
    const nextBody = nextDocument.body;
    nextBody.classList.add('page-enter');

    await loadMapAssets(nextBody);

    if (historyMode === 'push') {
        history.pushState({ scrollY: 0 }, '', url.href);
    } else if (window.location.href !== url.href) {
        history.replaceState({ ...history.state, scrollY }, '', url.href);
    }

    syncDocument(nextDocument);
    window.dispatchEvent(new Event('mintour:page-unload'));
    Alpine.destroyTree(oldBody);
    oldBody.replaceWith(nextBody);

    await activatePageScripts(nextBody);
    Alpine.initTree(nextBody);
    document.dispatchEvent(new Event('mintour:page-loaded'));

    if (url.hash) {
        const target = nextBody.ownerDocument.getElementById(decodeURIComponent(url.hash.slice(1)));
        if (target) target.scrollIntoView({ behavior: 'smooth' });
        else window.scrollTo(0, scrollY);
    } else {
        window.scrollTo(0, scrollY);
    }

    window.setTimeout(() => nextBody.classList.remove('page-enter'), 220);
}

async function navigate(url, { historyMode = 'push', scrollY = 0 } = {}) {
    const id = ++navigationId;
    if (navigationController) navigationController.abort();
    navigationController = new AbortController();

    try {
        const response = await fetch(url, {
            headers: { Accept: 'text/html' },
            signal: navigationController.signal,
        });
        if (!response.ok) throw new Error(`The page request failed (${response.status}).`);
        if (!response.headers.get('content-type')?.includes('text/html')) {
            throw new Error('The requested URL did not return an HTML page.');
        }
        if (id !== navigationId) return;

        const responseUrl = new URL(response.url);
        if (responseUrl.origin !== window.location.origin) {
            throw new Error('The page redirected outside this site.');
        }
        await renderPage(response, responseUrl, { historyMode, scrollY });
    } catch (error) {
        if (error.name === 'AbortError' || id !== navigationId) return;
        console.error('Smooth navigation failed; loading the page normally.', error);
        window.location.assign(url.href);
    } finally {
        if (id === navigationId) navigationController = null;
    }
}

function canNavigateLink(event, link, url) {
    return !event.defaultPrevented
        && event.button === 0
        && !event.metaKey
        && !event.ctrlKey
        && !event.shiftKey
        && !event.altKey
        && !link.hasAttribute('download')
        && !link.hasAttribute('target')
        && !link.hasAttribute('data-no-spa')
        && url.origin === window.location.origin
        && url.protocol.startsWith('http')
        && url.href !== window.location.href
        && !(url.pathname === window.location.pathname
            && url.search === window.location.search
            && url.hash);
}

document.addEventListener('click', event => {
    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (!link) return;

    const url = new URL(link.href, window.location.href);
    if (!canNavigateLink(event, link, url)) return;

    event.preventDefault();
    history.replaceState({ ...history.state, scrollY: window.scrollY }, '', window.location.href);
    navigate(url);
});

document.addEventListener('submit', event => {
    if (event.defaultPrevented) return;

    const form = event.target;
    if (!(form instanceof HTMLFormElement)
        || form.method.toLowerCase() !== 'get'
        || form.target
        || form.hasAttribute('data-no-spa')) return;

    const url = new URL(form.action, window.location.href);
    if (url.origin !== window.location.origin) return;

    const params = new URLSearchParams();
    const formData = event.submitter
        ? new FormData(form, event.submitter)
        : new FormData(form);
    for (const [name, value] of formData) {
        if (typeof value !== 'string') return;
        params.append(name, value);
    }
    url.search = params.toString();

    event.preventDefault();
    history.replaceState({ ...history.state, scrollY: window.scrollY }, '', window.location.href);
    navigate(url);
});

window.addEventListener('popstate', event => {
    navigate(new URL(window.location.href), {
        historyMode: 'none',
        scrollY: event.state?.scrollY || 0,
    });
});
