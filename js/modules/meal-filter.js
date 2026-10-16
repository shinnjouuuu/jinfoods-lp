/** Meals page: the すべて / いつもの食事 / ... buttons show only that kind of dish. */
export function initMealFilter() {
  const bar = document.querySelector('.dish-filter')
  const dishes = [...document.querySelectorAll('.dish')]
  const count = document.querySelector('.dish-count')
  if (!bar || !dishes.length) return

  const apply = (filter) => {
    bar.querySelectorAll('[data-filter]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.filter === filter))
    })
    let shown = 0
    dishes.forEach((dish) => {
      const visible = filter === 'all' || dish.dataset.category === filter
      dish.hidden = !visible
      if (visible) shown += 1
    })
    if (count) count.textContent = `${shown}品`
  }

  bar.addEventListener('click', (event) => {
    const button = event.target.closest('[data-filter]')
    if (button) apply(button.dataset.filter)
  })
}
