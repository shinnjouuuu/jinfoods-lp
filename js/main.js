import { runOpening } from './modules/opening.js'
import { splitChars } from './modules/split.js'
import { initHeader } from './modules/header.js'
import { initHeroSlides } from './modules/hero.js'
import { initMealFilter } from './modules/meal-filter.js'
import { initDishViewer } from './modules/dish-viewer.js?v=20261009-modal-title'
import { initContactForm } from './modules/contact-form.js?v=20261009-submit'
import { initMealsMarquee } from './modules/meals-marquee.js?v=20261009-scroll-v2'
import { initReveal } from './modules/reveal.js?v=20261005-1430'

const safely = (name, init) => {
  try {
    init()
  } catch (error) {
    // One broken effect must not stop the rest of the page from working.
    console.error(`[jin-foods] ${name} failed:`, error)
  }
}

safely('split', () => document.querySelectorAll('[data-split]').forEach(splitChars))
safely('header', initHeader)
safely('meal-filter', initMealFilter)
safely('dish-viewer', initDishViewer)
safely('contact-form', initContactForm)
safely('meals-marquee', initMealsMarquee)
safely('reveal', initReveal)

runOpening().then(() => {
  document.body.classList.add('is-ready')
  safely('hero', initHeroSlides)
})
