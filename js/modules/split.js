/**
 * Wraps each character of the element's lines in <span class="char">.
 * Inline elements inside a line (e.g. <em> for a red word) are kept around their characters.
 * The element keeps its aria-label, so screen readers read the sentence whole.
 */
export function splitChars(element) {
  const lines = element.children.length ? [...element.children] : [element]
  let index = 0

  const toChars = (text) => {
    const fragment = document.createDocumentFragment()
    ;[...text].forEach((character) => {
      const span = document.createElement('span')
      span.className = 'char'
      span.setAttribute('aria-hidden', 'true')
      span.style.setProperty('--i', String(index))
      span.textContent = character
      fragment.append(span)
      index += 1
    })
    return fragment
  }

  lines.forEach((line) => {
    const fragment = document.createDocumentFragment()
    line.childNodes.forEach((node) => {
      if (node.nodeType === Node.TEXT_NODE) {
        fragment.append(toChars(node.textContent.trim()))
      } else if (node.nodeType === Node.ELEMENT_NODE) {
        const wrapper = node.cloneNode(false)
        wrapper.append(toChars(node.textContent))
        fragment.append(wrapper)
      }
    })
    line.replaceChildren(fragment)
  })
}
