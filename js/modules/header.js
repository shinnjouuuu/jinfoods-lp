function initMenu(toggle, nav) {
  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open))
    nav.classList.toggle('is-open', open)
    document.body.classList.toggle('menu-open', open)
    // The visible label always reads MENU; the icon turns into an X when open.
    toggle.setAttribute('aria-label', open ? 'メニューを閉じる' : 'メニューを開く')
  }
  toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'))
  nav.addEventListener('click', (event) => {
    if (event.target.closest('a')) setOpen(false)
  })
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && nav.classList.contains('is-open')) {
      setOpen(false)
      toggle.focus()
    }
  })
  window.matchMedia('(min-width: 1024px)').addEventListener('change', () => setOpen(false))
}

export function initHeader() {
  const header = document.querySelector('.site-header')
  if (!header) return
  const toggle = header.querySelector('.menu-toggle')
  const nav = header.querySelector('.site-nav')
  if (toggle && nav) initMenu(toggle, nav)
}
