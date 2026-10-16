const hero = document.querySelector('.preview-hero')
if (hero) {
  const slides = [...hero.querySelectorAll('.preview-hero-slide')]
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)')
  let paused = reducedMotion.matches
  let visible = true
  let active = 0
  let timer
  const update = () => {
    clearInterval(timer)
    const running = !paused && visible && !document.hidden
    hero.classList.toggle('preview-paused', !running)
    if (!running) return
    hero.classList.add('preview-running')
    timer = setInterval(() => {
      slides[active].classList.remove('preview-is-active')
      slides[active].setAttribute('aria-hidden', 'true')
      active = (active + 1) % slides.length
      slides[active].classList.add('preview-is-active')
      slides[active].setAttribute('aria-hidden', 'false')
    }, 7000)
  }
  document.addEventListener('visibilitychange', update)
  reducedMotion.addEventListener('change', () => { paused = reducedMotion.matches; update() })
  new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; update() }).observe(hero)
  update()
}

// Restore the solid site header once the photo has scrolled out of view.
if (hero) {
  const header = document.querySelector('.site-header')
  if (header) {
    new IntersectionObserver(([entry]) => {
      header.classList.toggle('is-past-hero', !entry.isIntersecting)
    }, { rootMargin: '-84px 0px 0px 0px' }).observe(hero)
  }
}
