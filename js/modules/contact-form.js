/**
 * Contact page form: checks the required fields, shows a confirmation screen,
 * then sends it through FormSubmit without leaving the page.
 */
const FORM_ENDPOINT = 'https://formsubmit.co/ajax/info@jin-oita.com'
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const TEL_PATTERN = /^[0-9０-９+\-−ー（）() ]{10,20}$/

const FIELDS = [
  { name: 'topic', label: 'ご相談内容' },
  { name: 'name', label: 'お名前' },
  { name: 'org', label: '会社名・施設名' },
  { name: 'address', label: '住所' },
  { name: 'tel', label: '電話番号' },
  { name: 'email', label: 'メールアドレス' },
  { name: 'message', label: 'お問い合わせ内容' },
]

const readValues = (form) => {
  const data = new FormData(form)
  return Object.fromEntries(FIELDS.map(({ name }) => [name, String(data.get(name) ?? '').trim()]))
}

const findErrors = (values, agreed) => {
  const errors = {}
  if (!values.topic) errors.topic = 'ご相談内容を選んでください。'
  if (!values.name) errors.name = 'お名前を入力してください。'
  if (!values.tel) errors.tel = '電話番号を入力してください。'
  else if (!TEL_PATTERN.test(values.tel)) errors.tel = '電話番号は数字とハイフンで入力してください。'
  if (!values.email) errors.email = 'メールアドレスを入力してください。'
  else if (!EMAIL_PATTERN.test(values.email)) errors.email = 'メールアドレスの形式をご確認ください。'
  if (!agreed) errors.agree = 'プライバシーポリシーへの同意が必要です。'
  return errors
}

const showErrors = (form, errors) => {
  form.querySelectorAll('[data-error-for]').forEach((slot) => {
    const message = errors[slot.dataset.errorFor]
    slot.hidden = !message
    slot.textContent = message ?? ''
    form.querySelectorAll(`[name="${slot.dataset.errorFor}"]`).forEach((input) => {
      if (message) input.setAttribute('aria-invalid', 'true')
      else input.removeAttribute('aria-invalid')
    })
  })
  const first = Object.keys(errors)[0]
  if (first) form.querySelector(`[name="${first}"]`)?.focus()
}

const fillConfirm = (list, values) => {
  const rows = FIELDS.map(({ name, label }) => {
    const row = document.createElement('div')
    const term = document.createElement('dt')
    const detail = document.createElement('dd')
    term.textContent = label
    detail.textContent = values[name] || '（未入力）'
    row.append(term, detail)
    return row
  })
  list.replaceChildren(...rows)
}

export function initContactForm() {
  const form = document.getElementById('contact-form')
  const confirm = document.getElementById('form-confirm')
  if (!form || !confirm) return
  const list = confirm.querySelector('.form-confirm-list')
  const send = confirm.querySelector('[data-form="send"]')
  const back = confirm.querySelector('[data-form="back"]')
  const status = confirm.querySelector('[data-form="status"]')
  const complete = document.getElementById('form-complete')
  let valuesToSend = null
  let sending = false

  // Arriving from the recruit page (contact.html?type=recruit) pre-selects 採用について.
  if (new URLSearchParams(window.location.search).get('type') === 'recruit') {
    const recruit = form.querySelector('input[name="topic"][value="採用について"]')
    if (recruit) recruit.checked = true
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault()
    const values = readValues(form)
    const errors = findErrors(values, form.elements.agree.checked)
    showErrors(form, errors)
    if (Object.keys(errors).length) return
    fillConfirm(list, values)
    valuesToSend = values
    status.hidden = true
    status.textContent = ''
    delete status.dataset.state
    form.hidden = true
    confirm.hidden = false
    confirm.focus()
  })

  back.addEventListener('click', () => {
    if (sending) return
    confirm.hidden = true
    form.hidden = false
    form.querySelector('input, textarea')?.focus()
  })

  send.addEventListener('click', async () => {
    if (sending || !valuesToSend) return
    sending = true
    send.disabled = true
    back.disabled = true
    send.textContent = '送信中…'
    status.hidden = false
    status.dataset.state = 'sending'
    status.textContent = '送信しています。しばらくお待ちください。'

    try {
      const response = await fetch(FORM_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          ...valuesToSend,
          _subject: `【ホームページからのお問い合わせ】${valuesToSend.topic}`,
          _template: 'table',
          _honey: form.elements._honey.value,
        }),
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const result = await response.json()
      if (result.success !== true && result.success !== 'true') throw new Error('Submission rejected')

      valuesToSend = null
      form.reset()
      confirm.hidden = true
      complete.hidden = false
      complete.focus()
    } catch (error) {
      status.dataset.state = 'error'
      status.textContent = '送信できませんでした。時間をおいて再度お試しいただくか、お電話・メールでお問い合わせください。入力内容は保持されています。'
      console.error('[jin-foods] contact submission failed:', error)
      status.focus()
    } finally {
      sending = false
      send.disabled = false
      back.disabled = false
      send.textContent = 'この内容で送信する'
    }
  })
}
