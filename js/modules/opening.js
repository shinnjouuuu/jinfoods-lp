import { prefersReducedMotion } from './motion.js'

const SEEN_KEY = 'jin-opening-seen'
const HOLD_MS = 2600
const LIFT_MS = 1600

const hasSeenOpening = () => {
  try {
    return sessionStorage.getItem(SEEN_KEY) === '1'
  } catch {
    return false
  }
}

const markOpeningSeen = () => {
  try {
    sessionStorage.setItem(SEEN_KEY, '1')
  } catch {
    // Storage can be blocked (private mode); the opening just plays again.
  }
}

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms))

/** Plays the logo screen once per visit and resolves when the page may animate in. */
export async function runOpening() {
  const opening = document.querySelector('.opening')
  if (!opening) return

  if (prefersReducedMotion() || hasSeenOpening()) {
    opening.classList.add('is-gone')
    return
  }

  markOpeningSeen()
  await wait(HOLD_MS)
  opening.classList.add('is-done')
  // Let the hero start rising while the screen is still lifting.
  await wait(LIFT_MS * 0.4)
  setTimeout(() => opening.classList.add('is-gone'), LIFT_MS * 0.6)
}
