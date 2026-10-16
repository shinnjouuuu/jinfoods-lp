/**
 * Meals page: clicking a dish opens it large in a dialog, with its name and full
 * description beside the photo. ‹ › (or the arrow keys) step through the dishes that
 * are currently shown; Esc, the × button or a click outside closes it.
 */
export function initDishViewer() {
  const dialog = document.querySelector('.dish-viewer')
  if (!dialog || typeof dialog.showModal !== 'function') return

  const photo = dialog.querySelector('.dish-viewer-photo img')
  const tag = dialog.querySelector('.dish-viewer-tag')
  const name = dialog.querySelector('.dish-viewer-name')
  const note = dialog.querySelector('.dish-viewer-note')

  // Dishes hidden by the filter buttons are skipped.
  const visibleDishes = () => [...document.querySelectorAll('.dish')].filter((dish) => !dish.hidden)
  let current = null
  let opener = null

  const show = (dish) => {
    const img = dish.querySelector('.dish-photo img')
    if (!img) return
    current = dish
    photo.src = img.currentSrc || img.src
    photo.alt = img.alt
    photo.width = img.width
    photo.height = img.height
    tag.textContent = dish.querySelector('.dish-tag')?.textContent ?? ''
    const dishName = dish.querySelector('.dish-name')?.textContent?.trim() ?? ''
    name.textContent = dishName
    name.classList.toggle('is-long', Array.from(dishName).length >= 13)
    note.textContent = dish.querySelector('.dish-note')?.textContent ?? ''
  }

  const step = (by) => {
    const list = visibleDishes()
    if (!current || list.length < 2) return
    const index = (list.indexOf(current) + by + list.length) % list.length
    show(list[index])
  }

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.dish-open')
    if (!button) return
    opener = button
    show(button.closest('.dish'))
    dialog.showModal()
    dialog.querySelector('.dish-viewer-close')?.focus({ preventScroll: true })
    dialog.scrollTop = 0
    document.body.classList.add('menu-open')
  })

  dialog.addEventListener('click', (event) => {
    const nav = event.target.closest('[data-step]')
    if (nav) return step(Number(nav.dataset.step))
    // A click on the backdrop (the dialog itself, outside its content) closes it.
    if (event.target.closest('.dish-viewer-close') || event.target === dialog) dialog.close()
  })

  dialog.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowRight') step(1)
    if (event.key === 'ArrowLeft') step(-1)
  })

  dialog.addEventListener('close', () => {
    document.body.classList.remove('menu-open')
    opener?.focus()
  })
}
