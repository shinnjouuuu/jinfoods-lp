// Top page "Daily Meals": a menu of dish names written in vertical columns. Pointing at
// (or tapping) a name brings that dish's photo forward.
const ROOT = '.oshinagaki'

export function initOshinagaki() {
  const root = document.querySelector(ROOT)
  if (!root) return

  const items = [...root.querySelectorAll('.oshinagaki-item')]
  const figures = [...root.querySelectorAll('.oshinagaki-figure')]

  const show = (item) => {
    const targetId = item.getAttribute('aria-controls')
    items.forEach((other) => other.setAttribute('aria-pressed', String(other === item)))
    figures.forEach((figure) => {
      const active = figure.id === targetId
      figure.classList.toggle('is-active', active)
      figure.inert = !active
    })
  }

  items.forEach((item) => {
    item.addEventListener('click', () => show(item))
    item.addEventListener('focus', () => show(item))
    // Only real pointers switch on hover; a tap already arrives as a click.
    item.addEventListener('pointerenter', (event) => {
      if (event.pointerType === 'mouse') show(item)
    })
  })
}
