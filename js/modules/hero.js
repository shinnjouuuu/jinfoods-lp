import { prefersReducedMotion } from './motion.js'

const SLIDE_MS = 9000

/**
 * Cross-fades the hero photos and keeps the headline and the dash indicators in step.
 * The ‹ › buttons step through the photos; the middle button pauses and resumes.
 */
export function initHeroSlides() {
  const hero = document.querySelector('.hero')
  const slides = [...document.querySelectorAll('.hero-slide')]
  const titles = [...document.querySelectorAll('.hero-titles .hero-title')]
  const dots = [...document.querySelectorAll('.hero-dot')]
  const controls = document.querySelector('.hero-controls')
  const pauseButton = controls?.querySelector('[data-hero="pause"]')
  if (slides.length < 2) return

  document.documentElement.style.setProperty('--slide-ms', `${SLIDE_MS}ms`)
  let active = 0
  let timer = null
  // Reduced motion: no autoplay until the visitor presses play.
  let paused = prefersReducedMotion()

  // The active dash fills up over one slide's time.
  const setBar = (running) => {
    const bar = dots[active]?.firstElementChild
    dots.forEach((dot) => dot.firstElementChild?.classList.remove('is-running'))
    if (!bar || !running) return
    void bar.offsetWidth
    bar.classList.add('is-running')
  }

  const show = (next) => {
    slides[active].classList.remove('is-active')
    titles[active]?.classList.remove('is-active')
    dots[active]?.classList.remove('is-active')
    active = (next + slides.length) % slides.length
    slides[active].classList.add('is-active')
    titles[active]?.classList.add('is-active')
    dots[active]?.classList.add('is-active')
    // Some photos read better with the headline on the left.
    hero?.classList.toggle('is-text-left', slides[active].dataset.layout === 'text-left')
    setBar(timer !== null)
  }

  const stop = () => {
    clearInterval(timer)
    timer = null
    setBar(false)
  }
  const start = () => {
    if (timer || paused || document.hidden) return
    timer = setInterval(() => show(active + 1), SLIDE_MS)
    setBar(true)
  }
  const restart = () => {
    stop()
    start()
  }

  const setPaused = (value) => {
    paused = value
    controls?.classList.toggle('is-paused', paused)
    pauseButton?.setAttribute('aria-label', paused ? 'スライドを再生' : 'スライドを一時停止')
    restart()
  }

  controls?.addEventListener('click', (event) => {
    const action = event.target.closest('[data-hero]')?.dataset.hero
    if (action === 'prev' || action === 'next') {
      stop()
      show(active + (action === 'next' ? 1 : -1))
      start()
    }
    if (action === 'pause') setPaused(!paused)
  })

  document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()))
  setPaused(paused)
}
