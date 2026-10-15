/* =============================================================
 * FV B「紙焼き」を、マウスに合わせて傾ける（URL に ?fv=b があるときだけ。2026-10-05 新庄さん）
 *
 * - 配る動き（読み込み時に1回）は CSS（_fv.scss の is-prints-dealt と p-fv-print-deal）。
 *   2026-10-06 に GSAP から CSS に移した。GSAP を待つと、遅い回線で写真が出るまで 3秒前後かかったため
 * - ここは hover できる端末だけの「紙が手に持ったように傾く」動き（GSAP の quickTo。手前の紙ほど大きく＝data-depth）
 * - GSAP 3.12.5 は A（js/fv-steam.js）と同じ cdnjs から SRI 付きで、ページの読み込みが終わって手が空いてから読む
 * - 配る動きが終わってから傾け始める（途中で GSAP が触ると、動きの途中の位置と傾きを覚えてしまい、紙が戻らない）
 * - 動きを減らす設定・通信を節約する設定・タッチだけの端末では、何もしない
 * ========================================================== */
(() => {
  'use strict';

  const fv = document.querySelector('.js-fv');
  if (!fv || !fv.classList.contains('is-fv-b')) return;
  const mq = (q) => window.matchMedia(q).matches;
  const conn = navigator.connection;
  if (mq('(prefers-reduced-motion: reduce)') || !mq('(hover: hover)') || (conn && conn.saveData)) return;

  const GSAP = ['https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', 'sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg=='];
  const loadGsap = () => new Promise((resolve, reject) => {
    if (window.gsap) {
      resolve(window.gsap);
      return;
    }
    const s = document.createElement('script');
    [s.src, s.integrity] = GSAP;
    s.crossOrigin = 'anonymous';
    s.referrerPolicy = 'no-referrer';
    s.onload = () => (window.gsap ? resolve(window.gsap) : reject(new Error('gsap')));
    s.onerror = reject;
    document.head.appendChild(s);
  });

  // 配る動きが終わるのを待つ（まだ配り始めていなければ、始まるまで待つ。最長 10秒）
  const settled = () => new Promise((resolve) => {
    const started = Date.now();
    const check = () => {
      if (fv.classList.contains('is-prints-pending') && Date.now() - started < 10000) {
        setTimeout(check, 200);
        return;
      }
      const running = [...fv.querySelectorAll('.js-fv-print')].flatMap((p) => p.getAnimations());
      Promise.all(running.map((a) => a.finished)).then(resolve, resolve);
    };
    check();
  });

  const tiltOf = (p) => parseFloat(getComputedStyle(p).getPropertyValue('--print-r')) || 0;

  const start = () => Promise.all([loadGsap(), settled()]).then(([gsap]) => {
    // 幅によって出さない紙がある（md 未満は定食なし）。いま出ている紙だけを傾ける
    const prints = [...fv.querySelectorAll('.js-fv-print')].filter((p) => getComputedStyle(p).display !== 'none');
    const quick = (el, prop, duration) => gsap.quickTo(el, prop, { duration, ease: 'power3.out' });
    const tilts = prints.map((el) => ({
      rx: quick(el, 'rotationX', 0.6),
      ry: quick(el, 'rotationY', 0.6),
      x: quick(el, 'x', 0.8),
      k: parseFloat(el.dataset.depth) || 1,
    }));
    fv.addEventListener('pointermove', (e) => {
      const nx = e.clientX / window.innerWidth - 0.5;
      const ny = e.clientY / window.innerHeight - 0.5;
      tilts.forEach((t) => {
        t.ry(nx * 10 * t.k);
        t.rx(-ny * 8 * t.k);
        t.x(nx * 10 * t.k);
      });
    });
    fv.addEventListener('pointerleave', () => tilts.forEach((t) => {
      t.ry(0);
      t.rx(0);
      t.x(0);
    }));
    // 画面の幅が md をまたいだら、表の傾きが変わるので合わせ直す（GSAP が書いた傾きが前の幅のまま残らないように）
    window.matchMedia('(min-width: 768.02px)').addEventListener('change', () => gsap.set(prints, { rotation: (i, el) => tiltOf(el) }));
  }).catch(() => {});

  // 見出しと写真の表示を優先し、ページの読み込みが終わって手が空いてから GSAP を読む
  const kick = () => ('requestIdleCallback' in window ? window.requestIdleCallback(start, { timeout: 1500 }) : setTimeout(start, 200));
  if (document.readyState === 'complete') kick();
  else window.addEventListener('load', kick, { once: true });
})();
