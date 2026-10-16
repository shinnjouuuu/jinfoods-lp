/** Show the page-top control once the opening content has moved out of view. */
export function initBackTop() {
  const button = document.querySelector('.back-top')
  if (!button) return

  let ticking = false

  const update = () => {
    const keyboardFocus = button.matches(':focus-visible')
    button.classList.toggle('is-visible', window.scrollY > 360 || keyboardFocus)
    ticking = false
  }

  const queueUpdate = () => {
    if (ticking) return
    ticking = true
    requestAnimationFrame(update)
  }

  window.addEventListener('scroll', queueUpdate, { passive: true })
  button.addEventListener('focus', update)
  button.addEventListener('blur', update)
  update()
}
