const reducedQuery = window.matchMedia('(prefers-reduced-motion: reduce)')

export const prefersReducedMotion = () => reducedQuery.matches

/** Calls `update` at most once per frame while the page scrolls or resizes. */
export function onScrollFrame(update) {
  let queued = false
  const request = () => {
    if (queued) return
    queued = true
    requestAnimationFrame(() => {
      queued = false
      update()
    })
  }
  window.addEventListener('scroll', request, { passive: true })
  window.addEventListener('resize', request)
  request()
  return request
}

export const clamp = (value, min, max) => Math.min(Math.max(value, min), max)
