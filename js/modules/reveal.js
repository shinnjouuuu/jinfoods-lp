import { prefersReducedMotion } from './motion.js'

// Shared reveal targets for every page, including individual information rows.
// Selectors that are missing on a page are simply skipped.
const PHOTO_TARGETS = [
  // Top page
  '.message-photo', '.service-item', '.recruit-bg',
  // Sub-pages
  '.split-photo', '.reason-photo', '.offer-card', '.topic-card', '.event-grid li',
  '.dish', '.job-card', '.step-list li', '.value-list li', '.post-photos figure',
  '.news-card', '.access-map', '.meals-selection',
]
const TEXT_TARGETS = [
  // Top page
  '.message-copy', '.rt-ma', '.services-head', '.meals-head', '.recruit-copy', '.news-head',
  '.news-list > li', '.contact-inner > .label', '.contact-title', '.contact-lead',
  '.contact-card', '.contact-note',
  // Sub-pages
  '.page-hero-copy', '.page-heading', '.page-section > .label', '.page-section-lead', '.prose',
  '.data-list > div', '.timeline > li', '.facility-list > li', '.point-list > li',
  '.form-tags', '.reason-copy', '.form-scale', '.dish-index', '.dish-chapter-head',
  '.event-season-heading', '.access-copy', '.form-row', '.form-actions',
  '.post-hero', '.post-body', '.post-nav', '.sitemap-list > li',
  '.philosophy-head', '.policy > *', '.direct-card', '.page-section > .note',
  'main .text-link', 'main .button',
]
const STAGGER_MS = 90
const STAGGER_STEPS = 4

/**
 * Reveals each block once, keeping content readable when returning to it.
 */
export function initReveal() {
  if (prefersReducedMotion() || !('IntersectionObserver' in window)) return

  const collect = (selectors) => selectors.flatMap((selector) => [...document.querySelectorAll(selector)])
  const photos = collect(PHOTO_TARGETS)
  const texts = collect(TEXT_TARGETS)
  // A block inside another revealed block would blur twice, so only the outer one moves.
  const all = [...new Set([...photos, ...texts])]
  const isNested = (el) => all.some((other) => other !== el && other.contains(el))

  const targets = [
    ...photos.filter((el) => !isNested(el)).map((el) => el.classList.add('fx', 'fx--photo') ?? el),
    ...texts.filter((el) => !isNested(el)).map((el) => el.classList.add('fx', 'fx--text') ?? el),
  ]

  // Items shown side by side in a grid start one after another.
  targets.forEach((el) => {
    const siblings = [...el.parentElement.children].filter((child) => child.classList.contains('fx'))
    if (siblings.length < 2) return
    const index = siblings.indexOf(el) % STAGGER_STEPS
    el.style.setProperty('--fx-delay', `${index * STAGGER_MS}ms`)
  })

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(({ target, isIntersecting }) => {
        if (!isIntersecting) return
        target.classList.add('is-shown')
        observer.unobserve(target)
      })
    },
    { rootMargin: '0px 0px -4% 0px', threshold: [0, 0.01] },
  )
  targets.forEach((el) => observer.observe(el))

  // Keyboard users should never focus a control that is still hidden.
  document.addEventListener('focusin', ({ target }) => {
    const block = target.closest('.fx')
    if (!block) return
    block.style.setProperty('--fx-delay', '0ms')
    block.classList.add('is-shown')
    observer.unobserve(block)
  })
}
