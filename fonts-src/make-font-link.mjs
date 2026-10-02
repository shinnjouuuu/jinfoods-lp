// LP の各ページで Webフォント（見出しの Zen角ゴシック 700・英数字の Jost）で描かれる字を集め、
// Google Fonts に「その字だけ」を頼む指定（text=）を作って、各ページの <head> の
// <!-- fonts:start --> 〜 <!-- fonts:end --> の間を書き換える。
//
//   cd ~/projects/lp/jinfoods-lp && node fonts-src/make-font-link.mjs
//
// 見出し・ボタン・ラベルなど、Zen角ゴ／Jost を使う所の文言を足したり変えたりしたら、必ず流し直す。
// 流し直さないと、新しい字だけ端末の書体で出る（見出しの中で書体が混ざる）。
// 2026-10-01 作成。書体ファイルは 28本・273KB → 2〜3本・57KB（README「書体の読み込み」）
import { spawn } from 'node:child_process';
import { readFileSync, writeFileSync, readdirSync, mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, dirname } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const FAMILIES = 'family=Jost:wght@500;700&family=Zen+Kaku+Gothic+New:wght@700&display=swap';
// JS が後から入れる数字・英字（カウントアップなど）と、よく使う記号の保険
const SAFETY = Array.from({ length: 0x7e - 0x21 + 1 }, (_, i) => String.fromCharCode(0x21 + i)).join('') + '、。・ー（）／〜→';
const PAGES = readdirSync(ROOT).filter((f) => f.endsWith('.html') && f !== 'ogp.html').sort();
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// ページの中で、その書体で描かれうる字を集める（非表示の要素・::before／::after の content も含む）
const COLLECT = `(() => {
  const zen = new Set(), jost = new Set();
  const add = (set, text) => { for (const ch of text) if (ch.trim()) set.add(ch); };
  const fam = (cs) => cs.fontFamily.split(',')[0].replace(/["']/g, '').trim();
  for (const el of document.querySelectorAll('body *')) {
    const own = [...el.childNodes].filter((n) => n.nodeType === 3).map((n) => n.textContent).join('');
    const f = fam(getComputedStyle(el));
    if (f === 'Zen Kaku Gothic New') add(zen, own);
    if (f === 'Jost') add(jost, own);
    for (const pseudo of ['::before', '::after']) {
      const p = getComputedStyle(el, pseudo); const c = p.content;
      if (c && /^["']/.test(c)) { const t = c.slice(1, -1); const pf = fam(p); if (pf === 'Zen Kaku Gothic New') add(zen, t); if (pf === 'Jost') add(jost, t); }
    }
  }
  return [...zen].join('') + [...jost].join('');
})()`;

const profile = mkdtempSync(join(tmpdir(), 'font-link-'));
const chrome = spawn(CHROME, ['--headless=new', '--remote-debugging-port=0', `--user-data-dir=${profile}`, '--no-first-run', 'about:blank']);
const wsUrl = await new Promise((resolve, reject) => {
  chrome.stderr.on('data', (d) => { const m = String(d).match(/ws:\/\/\S+/); if (m) resolve(m[0]); });
  chrome.on('exit', () => reject(new Error('Chrome が起動しなかった')));
});
const ws = new WebSocket(wsUrl);
await new Promise((r) => ws.addEventListener('open', r));
let id = 0; const pend = new Map();
ws.addEventListener('message', (e) => { const m = JSON.parse(e.data); if (m.id && pend.has(m.id)) { pend.get(m.id)(m); pend.delete(m.id); } });
const send = (method, params = {}, sessionId) => new Promise((res) => { const msg = { id: ++id, method, params }; if (sessionId) msg.sessionId = sessionId; pend.set(msg.id, res); ws.send(JSON.stringify(msg)); });

const chars = new Set(SAFETY);
for (const page of PAGES) {
  const { result: { targetId } } = await send('Target.createTarget', { url: 'about:blank' });
  const { result: { sessionId } } = await send('Target.attachToTarget', { targetId, flatten: true });
  await send('Page.enable', {}, sessionId);
  await send('Page.navigate', { url: pathToFileURL(join(ROOT, page)).href }, sessionId);
  await sleep(1500);  // main.js が入れる文言（カウントアップの数字など）を待つ
  const r = await send('Runtime.evaluate', { expression: COLLECT, returnByValue: true }, sessionId);
  for (const ch of r.result.result.value) chars.add(ch);
  await send('Target.closeTarget', { targetId });
}
ws.close();
// Chrome が閉じきってから一時プロファイルを消す（閉じる前に消すと書き込み中で失敗する）
await new Promise((r) => { chrome.once('exit', r); chrome.kill(); });
try { rmSync(profile, { recursive: true, force: true }); } catch { /* 残っても OS の一時フォルダなので害は無い */ }

const text = [...chars].sort().join('');
const url = `https://fonts.googleapis.com/css2?${FAMILIES}&text=${encodeURIComponent(text)}`;
// 描画を止めない読み込み（preload して、届いたら stylesheet に替える）。JS が無いときは noscript で普通に読む
const block = [
  '<!-- fonts:start -->',
  '<!-- Webフォント：見出しの Zen角ゴシック 700 と英数字の Jost 500・700 だけ（本文は端末の書体。DESIGN.md §3）。',
  `     使う字だけを頼む（text=、${chars.size}字）。描画は止めない。文言を変えたら node fonts-src/make-font-link.mjs を流し直す -->`,
  '<link rel="preconnect" href="https://fonts.googleapis.com">',
  '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
  `<link rel="preload" as="style" href="${url}" onload="this.onload=null;this.rel='stylesheet'">`,
  `<noscript><link rel="stylesheet" href="${url}"></noscript>`,
  '<!-- fonts:end -->',
].join('\n');

let changed = 0;
for (const page of PAGES) {
  const path = join(ROOT, page);
  const html = readFileSync(path, 'utf8');
  const a = html.indexOf('<!-- fonts:start -->');
  const b = html.indexOf('<!-- fonts:end -->');
  if (a < 0 || b < 0) { console.log(`目印が無いので飛ばした: ${page}`); continue; }
  const next = html.slice(0, a) + block + html.slice(b + '<!-- fonts:end -->'.length);
  if (next !== html) { writeFileSync(path, next); changed++; }
}
console.log(`字 ${chars.size}字・URL ${url.length}文字・書き換えたページ ${changed}／${PAGES.length}`);
