/** Scroll the meal gallery inside its viewport, avoiding one very wide animated layer. */
export function initMealsMarquee() {
  const marquee = document.querySelector('.meals-marquee')
  const track = marquee?.querySelector('.meals-marquee-track')
  const firstSet = track?.querySelector('.meals-set')
  if (!marquee || !track || !firstSet) return

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
  const duration = Number(track.dataset.duration) || 120
  let inView = false
  let pausedByPointer = false
  let pausedByFocus = false
  let frameId = 0
  let lastTime = 0
  let offset = 0
  let loopWidth = 0

  const measure = () => {
    const card = firstSet.querySelector('.meal-card')
    if (!card) return
    const style = getComputedStyle(firstSet)
    const gap = parseFloat(style.columnGap) || 0
    const trailingSpace = parseFloat(style.paddingRight) || 0
    loopWidth = firstSet.children.length * card.getBoundingClientRect().width +
      (firstSet.children.length - 1) * gap + trailingSpace
    if (!loopWidth) return
    track.querySelectorAll('.meals-set').forEach((set) => { set.style.width = `${loopWidth}px` })
    track.style.width = `${loopWidth * (reducedMotion.matches ? 1 : 2)}px`
    offset %= loopWidth
    marquee.scrollLeft = offset
  }

  measure()
  window.addEventListener('resize', measure)

  const canMove = () => inView && !pausedByPointer && !pausedByFocus &&
    !reducedMotion.matches && document.visibilityState === 'visible'

  const frame = (now) => {
    frameId = 0
    if (!canMove()) return

    if (loopWidth > 0 && lastTime) {
      const elapsed = Math.min(now - lastTime, 64) / 1000
      offset += loopWidth * elapsed / duration
      if (offset >= loopWidth) offset -= loopWidth
      marquee.scrollLeft = offset
    }
    lastTime = now
    frameId = requestAnimationFrame(frame)
  }

  const update = () => {
    if (canMove()) {
      if (!frameId) {
        offset = marquee.scrollLeft
        frameId = requestAnimationFrame(frame)
      }
    } else {
      cancelAnimationFrame(frameId)
      frameId = 0
      lastTime = 0
    }
  }

  marquee.addEventListener('mouseenter', () => { pausedByPointer = true; update() })
  marquee.addEventListener('mouseleave', () => { pausedByPointer = false; update() })
  marquee.addEventListener('focusin', () => { pausedByFocus = true; update() })
  marquee.addEventListener('focusout', () => {
    pausedByFocus = marquee.contains(document.activeElement)
    update()
  })
  document.addEventListener('visibilitychange', update)
  reducedMotion.addEventListener('change', () => { measure(); update() })
  new IntersectionObserver(([entry]) => {
    inView = entry.isIntersecting
    update()
  }, { rootMargin: '200px 0px' }).observe(marquee)

  // Load the next few pictures before they enter the visible part of the row.
  const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(({ target, isIntersecting }) => {
      if (isIntersecting) {
        target.loading = 'eager'
        imageObserver.unobserve(target)
      }
    })
  }, { root: marquee, rootMargin: '0px 800px' })
  track.querySelectorAll('img').forEach((image) => imageObserver.observe(image))
}
