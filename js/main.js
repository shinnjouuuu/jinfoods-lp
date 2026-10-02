/* =============================================================
 * jinfoods-lp / main.js
 *
 * 方針
 * - 素のJS。IntersectionObserver を軸に、外部ライブラリは使わない
 * - DOMの取得は js- 接頭辞のクラスだけを見る。見た目のクラス（l- / c- / p-）は
 *   セレクタに使わない。デザイン変更でJSが壊れるのを防ぐため
 * - 状態の付け外しは is- / aria-* に寄せ、見た目の指定はCSS側が持つ
 * - prefers-reduced-motion: reduce のときは動きを全部止める
 * ============================================================= */
(() => {
  'use strict';

  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* -----------------------------------------------------------
   * ヘッダー：スクロールしたら影を出す
   * scroll イベントを直接使わず、先頭に置いた番兵を監視する。
   * scroll ハンドラは1フレームに何度も走るので、監視で済む処理は監視でやる
   * --------------------------------------------------------- */
  const initHeader = () => {
    const header = document.querySelector('.js-header');
    if (!header) return;

    const sentinel = document.createElement('div');
    sentinel.setAttribute('aria-hidden', 'true');
    sentinel.style.cssText = 'position:absolute;top:0;left:0;width:1px;height:1px;';
    document.body.prepend(sentinel);

    new IntersectionObserver(
      ([entry]) => header.classList.toggle('is-scrolled', !entry.isIntersecting),
      { threshold: 0 }
    ).observe(sentinel);
  };

  /* -----------------------------------------------------------
   * ドロワー
   * hidden の付け外しも行う。CSSのvisibilityだけで隠すと
   * 閉じている間もキーボードでリンクを拾えてしまうため
   * --------------------------------------------------------- */
  const initDrawer = () => {
    const toggle = document.querySelector('.js-drawer-toggle');
    const drawer = document.querySelector('.js-drawer');
    if (!toggle || !drawer) return;

    const label = toggle.querySelector('.js-drawer-label');
    // 開いている間は、後ろ（本文・フッター・SP 固定バー・WP の「本文へスキップ」）に inert を付けて Tab で抜けないようにする。
    // ヘッダー（×のボタン）とドロワーはこの外にあるので押せるまま
    const behind = document.querySelectorAll('main, .l-footer, .js-spbar, .js-skip');

    const setOpen = (open) => {
      toggle.setAttribute('aria-expanded', String(open));
      document.body.classList.toggle('is-drawer-open', open);
      // SP 固定バーは、閉じたときも隠れたまま（is-shown が無い）なら inert を残す（initSpbar と同じ決まり）
      behind.forEach((el) => { el.inert = open || (el.classList.contains('js-spbar') && !el.classList.contains('is-shown')); });
      // ハンバーガーは開くと×に変形する。読み上げ側にも役割の変化を伝える
      if (label) label.textContent = open ? 'メニューを閉じる' : 'メニューを開く';

      if (open) {
        drawer.hidden = false;
        // hidden を外した直後だと transition が走らないので次フレームまで待つ
        requestAnimationFrame(() => drawer.classList.add('is-open'));
      } else {
        drawer.classList.remove('is-open');
        const hide = () => { drawer.hidden = true; };
        // 300ms は CSS の --dur（0.3s）と揃えている。
        // 閉じるアニメーションが終わってから hidden を付けないと、
        // 消える途中で消滅して見える
        prefersReduced ? hide() : setTimeout(hide, 300);
      }
    };

    toggle.addEventListener('click', () => {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    // ドロワー内のリンクを踏んだら閉じる
    drawer.addEventListener('click', (e) => {
      if (e.target.closest('a')) setOpen(false);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setOpen(false);
        toggle.focus();
      }
    });

    // PC幅に戻したときに開いたまま残らないようにする
    // 1000.02px は Sass の mq-up("nav") と同じ値。
    // +0.02px しているのは「1000px ちょうどはSP扱い」に揃えるため
    // （CSSとJSで境界がずれると、ナビが無い幅が生まれる）
    window.matchMedia('(min-width: 1000.02px)').addEventListener('change', (e) => {
      if (e.matches) setOpen(false);
    });
  };

  /* -----------------------------------------------------------
   * アンカーのスムーススクロール
   * CSSの scroll-padding-top で固定ヘッダーぶんは逃がしてあるので、
   * ここでは既定動作に任せ、ドロワー閉時のズレ対策だけを持つ
   * --------------------------------------------------------- */
  const initAnchor = () => {
    document.querySelectorAll('.js-anchor').forEach((link) => {
      link.addEventListener('click', (e) => {
        const id = link.getAttribute('href');
        if (!id || !id.startsWith('#')) return;
        const target = document.querySelector(id);
        if (!target) return;

        e.preventDefault();
        target.scrollIntoView({
          behavior: prefersReduced ? 'auto' : 'smooth',
          block: 'start',
        });
        // フォーカスも移す（キーボード・読み上げ利用者が迷子にならないように）
        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
      });
    });
  };

  /* -----------------------------------------------------------
   * スクロールで下から出す
   * 子要素を持つブロックは、子を 80ms ずつ遅らせて出す
   * --------------------------------------------------------- */
  const initReveal = () => {
    const targets = document.querySelectorAll('.js-reveal');
    if (!targets.length) return;

    if (prefersReduced) {
      targets.forEach((el) => el.classList.add('is-inview'));
      return;
    }

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-inview');
          io.unobserve(entry.target); // 一度出したら監視をやめる
        });
      },
      // threshold 0.12 … 要素の12%が見えたら出す。0だと画面の縁で出て動きが目立たない
      // rootMargin -8%  … 判定ラインを画面下から8%内側に上げる。
      //                   下端ぴったりで出すと、出た瞬間に画面外へ流れることがある
      { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );

    targets.forEach((el, i) => {
      // リロードでスクロール位置が復元された場合、すでに通り過ぎた要素は
      // 一度も交差しないので opacity:0 のまま残る。先に出しておく
      if (el.getBoundingClientRect().bottom < 0) {
        el.classList.add('is-inview');
        return;
      }
      // 同じ行に並ぶ要素が一斉に出ると硬いので、少しずつずらす
      // 同じ行に並ぶ要素を80msずつずらす。4個で頭に戻す（% 4）ので、
      // ページ全体で遅延が積み上がって最後の要素が何秒も遅れる、という事故を防ぐ
      el.style.transitionDelay = `${Math.min(i % 4, 3) * 80}ms`;
      io.observe(el);
    });
  };

  /* -----------------------------------------------------------
   * 数字のカウントアップ
   * 到達値は data-count-to で持つ。8 や 50 のような小さい数なので
   * 所要時間は固定で十分（桁で割ると1桁の数が一瞬で終わる）
   *
   * HTML には最初から本当の値（8・50）を書く。0 に戻すのは、動かすと決まったこのときだけ。
   * 以前は HTML が「0」で、JS が無い・失敗した・スクロールせずに読み上げた・クローラーでは
   * 「0施設」「0名」と伝わっていた（2026-10-01 hallmark check）。
   * 読み上げには隣の .u-vh（「8施設」）を読ませ、動く数字は aria-hidden にしてある
   * --------------------------------------------------------- */
  const initCount = () => {
    const nums = document.querySelectorAll('.js-count');
    if (!nums.length) return;

    const settle = (el) => {
      el.textContent = el.dataset.countTo;
    };

    if (prefersReduced) {
      nums.forEach(settle);
      return;
    }

    // 読み込んだ時点で 6割以上見えている数字は、本当の値のまま動かさない（8→0→8 のちらつきを出さない）。
    // 6割は下の IntersectionObserver の発火条件と同じ。端から少し覗いているだけの数字は 0 にして、見えたら数える。
    // 実測（2026-10-01）：会社概要の数字は 375×812・390×844 で最初から見えている → 動かない。
    // トップは全幅で最初の画面の外 → 0 から数える
    const pending = [];
    nums.forEach((el) => {
      if (!Number.isFinite(Number(el.dataset.countTo))) return;
      const r = el.getBoundingClientRect();
      const seen = r.height ? (Math.min(r.bottom, window.innerHeight) - Math.max(r.top, 0)) / r.height : 0;
      if (seen >= 0.6) return settle(el);
      el.textContent = '0';
      pending.push(el);
    });
    if (!pending.length) return;

    const run = (el) => {
      const to = Number(el.dataset.countTo);
      if (!Number.isFinite(to)) return settle(el);

      // カウントアップの所要時間。0.9秒。
      // 出す数字が 8 / 1 / 50 と小さいので、桁数で割らず固定でよい
      // （桁数で割ると1桁の数が一瞬で終わって気づかれない）
      const duration = 900;
      let start = null;

      const step = (now) => {
        if (start === null) start = now;
        const p = Math.min((now - start) / duration, 1);
        // 終盤を減速させる（ease-out cubic）。3乗なのは、
        // 2乗だと止まり方が甘く、4乗だと最後がもたつくため
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = String(Math.round(to * eased));
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          run(entry.target);
          io.unobserve(entry.target);
        });
      },
      { threshold: 0.6 }
    );
    pending.forEach((el) => io.observe(el));
  };

  /* -----------------------------------------------------------
   * 食事形態の5段階を、見えたら順に出す（1回だけ）
   *   トップの強み（.js-strength-steps）：左から順に。2026-09-30 に紙芝居をやめて置き換え（理由は _strength.scss の冒頭）
   *   事業内容ページ（.js-texture-steps）：上から順に。2026-10-01 の D案（_texture.scss）
   * 1段ごとの遅れは CSS（--i と $_step-stagger）が持つ。ここは状態を付けるだけ。
   * JS が無い／動きを減らす設定では is-armed を付けないので、最初から全部出ている
   * --------------------------------------------------------- */
  const initStrengthSteps = () => {
    if (prefersReduced) return;
    document.querySelectorAll('.js-strength-steps, .js-texture-steps').forEach((steps) => {
      // リロードでもう通り過ぎた位置に戻った場合は、隠さずそのまま出しておく
      if (steps.getBoundingClientRect().bottom < 0) return;

      steps.classList.add('is-armed');
      // 要素ごとに observer を分ける（1つを共有して disconnect すると、先に入った方でもう片方も止まる）。
      // 事業内容の5段階は縦に5行（SP で約370px）あるので 0.25。0.5 だと 320×568 で上の行が見えているのに出なかった
      const io = new IntersectionObserver(
        ([entry]) => {
          if (!entry.isIntersecting) return;
          steps.classList.add('is-drawn');
          io.disconnect();
        },
        { threshold: steps.matches('.js-texture-steps') ? 0.25 : 0.5 }
      );
      io.observe(steps);
    });
  };

  /* -----------------------------------------------------------
   * パララックス（数字セクションの背景のみ）
   * 唯一の適用箇所。増やすとスクロールが重くなり、
   * このサイトの読み手（施設の担当者・求職者）には邪魔になる
   * --------------------------------------------------------- */
  const initParallax = () => {
    const el = document.querySelector('.js-parallax');
    if (!el || prefersReduced) return;

    const section = el.closest('section');
    if (!section) return;

    let inView = false;
    let ticking = false;

    const update = () => {
      const rect = section.getBoundingClientRect();
      // セクションの通過率を -1〜1 に正規化し、背景を上下に振る
      const progress = (rect.top + rect.height / 2 - window.innerHeight / 2) /
        (window.innerHeight / 2 + rect.height / 2);
      // 背景を上下に最大6%動かす。背景は124%の高さで置いてあるので、
      // 6%動かしても地の色が見えない（余白 12% の範囲内に収まる）
      el.style.transform = `translate3d(0, ${(progress * 6).toFixed(2)}%, 0)`;
      ticking = false;
    };

    const onScroll = () => {
      if (!inView || ticking) return;
      ticking = true;
      requestAnimationFrame(update);
    };

    new IntersectionObserver(
      ([entry]) => {
        inView = entry.isIntersecting;
        if (inView) update();
      },
      { threshold: 0 }
    ).observe(section);

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
  };

  /* -----------------------------------------------------------
   * SP固定CTAバー
   * トップ：FVを抜けたら出す。FV内にCTAが2本あるので、そこでは重複させない
   * 下層：ページ見出しにCTAが無いので、最初から出しておく
   *   （以前は FV が無いと何もしなかったため、下層ページでは一度も出ていなかった。2026-09-30 修正）
   * --------------------------------------------------------- */
  const initSpbar = () => {
    const bar = document.querySelector('.js-spbar');
    if (!bar) return;

    // 画面の下に隠れている間は inert にして、Tab で止まらないようにする（前は FV の間も Shift+Tab で
    // 見えないボタンにフォーカスが行っていた。2026-10-02、html-quality のレビュー）。
    // ドロワーが開いている間はドロワー側が inert を付けているので、そのあいだは外さない
    const setShown = (shown) => {
      bar.classList.toggle('is-shown', shown);
      bar.inert = !shown || document.body.classList.contains('is-drawer-open');
    };

    const fv = document.querySelector('.js-fv');
    if (!fv) {
      setShown(true);
      return;
    }

    setShown(false);
    new IntersectionObserver(
      ([entry]) => setShown(!entry.isIntersecting),
      // FVの上から40%を過ぎたら固定バーを出す。
      // 0%（FVを完全に抜けた時点）だと出るのが遅く、CTAを取りこぼす
      { threshold: 0, rootMargin: '-40% 0px 0px 0px' }
    ).observe(fv);
  };

  /* --------------------------------------------------------- */
  /* -----------------------------------------------------------
   * 会社概要：拠点の概略図と表の連動
   * 表の「所在地」の行が画面の中央に来たら本社を脈打たせ、
   * 「受託施設」の行で地区を順に灯す。
   * 地図が横に止まっているときだけ動かす（_company.scss の sticky と同じ条件：
   * 幅 1000.02px 以上かつ高さ 720px 以上）。それ以外は地図が表を読む位置の外にあるので、
   * 最初から全部灯した状態のままにする
   * --------------------------------------------------------- */
  const initAreaMap = () => {
    const map = document.querySelector('.js-area-map');
    const rows = document.querySelectorAll('.js-area-spot');
    if (!map || !rows.length || prefersReduced) return;
    if (!window.matchMedia('(min-width: 1000.02px) and (min-height: 720px)').matches) return;

    map.classList.add('is-armed');

    // 地区を灯すのは「受託施設の行が画面の中央より上に来たら」。
    // IntersectionObserver だと、行を一気に飛び越えたとき（#profile へ直接来た・速いスクロール）に
    // 呼ばれず地図が薄いまま残るので、ここだけスクロール量で見る。一度灯したら監視をやめる
    const sitesRow = document.querySelector('.js-area-spot[data-area-spot="sites"]');
    let ticking = false;
    const lightSites = () => {
      ticking = false;
      if (!sitesRow || sitesRow.getBoundingClientRect().top > window.innerHeight / 2) return;
      map.classList.add('is-sites');
      window.removeEventListener('scroll', onScroll);
    };
    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(lightSites);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    lightSites();

    // 行の強調と本社の脈打ちは「いま読んでいる行」だけでよいので IntersectionObserver で見る
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          entry.target.classList.toggle('is-spot', entry.isIntersecting);
          if (entry.target.dataset.areaSpot === 'hq' && entry.isIntersecting) {
            // 脈打ちを毎回最初から再生するため、一度外して付け直す
            map.classList.remove('is-hq');
            void map.offsetWidth;
            map.classList.add('is-hq');
          }
        });
      },
      // 画面の上下45%を除いた、中央10%の帯に入った行を「いま読んでいる行」とみなす
      { rootMargin: '-45% 0px -45% 0px' }
    );
    rows.forEach((row) => io.observe(row));
  };

  /* -----------------------------------------------------------
   * 会社概要：沿革の年表
   * 見えたら線を上から伸ばし、年を順に出す（1回だけ）
   * --------------------------------------------------------- */
  const initHistory = () => {
    const list = document.querySelector('.js-history');
    if (!list || prefersReduced) return;

    // すでに通り過ぎている（リロードでスクロール位置が戻った等）ときは、隠さずに終える
    if (list.getBoundingClientRect().bottom < 0) return;

    list.classList.add('is-armed');
    const io = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        list.classList.add('is-drawn');
        io.disconnect();
      },
      { threshold: 0.4 }
    );
    io.observe(list);
  };

  /* -----------------------------------------------------------
   * トップ：受託施設一覧の地図（施設名のタグは最初から出ている）
   * タグ（点のボタン）を押すと、aria-controls で指した地図の下の詳細の hidden を外す。
   * 状態は aria-expanded だけで持つ（見た目も CSS の [aria-expanded="true"] で当てる）。
   * 開くのは1つだけ。同じタグをもう一度押すか Esc で閉じる。
   * JS が無いときは、タグだけが出て詳細は出ない
   * --------------------------------------------------------- */
  const initFacilityMap = () => {
    const pins = document.querySelectorAll('.js-facility-pin');
    if (!pins.length) return;

    const detailOf = (pin) => document.getElementById(pin.getAttribute('aria-controls'));
    const isOpen = (pin) => pin.getAttribute('aria-expanded') === 'true';

    const setOpen = (pin, open) => {
      pin.setAttribute('aria-expanded', String(open));
      const detail = detailOf(pin);
      if (detail) detail.hidden = !open;
    };

    const closeAll = () => pins.forEach((pin) => setOpen(pin, false));

    pins.forEach((pin) => {
      pin.addEventListener('click', () => {
        const willOpen = !isOpen(pin);
        closeAll();
        if (willOpen) setOpen(pin, true);
      });
    });

    // 開いているものがあるときだけ閉じる（ほかの Esc の処理とぶつけない）
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && [...pins].some(isOpen)) closeAll();
    });
  };

  /* -----------------------------------------------------------
   * トップ：数字で見る — 稼働時間の帯
   * 見えたら朱の帯を左から伸ばす（1回だけ）。帯は 0:00〜24:00 で、朱は稼働している範囲だけ
   * --------------------------------------------------------- */
  const initStatsBar = () => {
    const bar = document.querySelector('.js-stats-bar');
    if (!bar || prefersReduced) return;

    // すでに通り過ぎている（リロードでスクロール位置が戻った等）ときは、伸びた状態のまま
    if (bar.getBoundingClientRect().bottom < 0) return;

    bar.classList.add('is-armed');
    const io = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        bar.classList.add('is-drawn');
        io.disconnect();
      },
      { threshold: 0.6 }
    );
    io.observe(bar);
  };

  /* -----------------------------------------------------------
   * 写真の拡大表示（c-viewer。食事ギャラリーのページ・WP のギャラリー一覧）
   * <dialog> の showModal() を使う：最前面に出る・後ろは操作できない（Tab で外へ抜けない）・Esc で閉じる、を
   * ブラウザに任せる。開くリンク（.js-viewer-item）の href は大きい画像（WP は投稿ページ）で、
   * JS が動かないときはそのまま開く。大きい画像は data-viewer-src（無ければ href）、WebP は data-viewer-webp、
   * 説明は格子の写真の代わりの文。閉じたら押した写真にフォーカスを戻す
   * --------------------------------------------------------- */
  const initViewer = () => {
    const dialog = document.querySelector('.js-viewer');
    const items = [...document.querySelectorAll('.js-viewer-item')];
    if (!dialog || !items.length || typeof dialog.showModal !== 'function') return;

    const img = dialog.querySelector('.js-viewer-img');
    const cap = dialog.querySelector('.js-viewer-cap');
    const count = dialog.querySelector('.js-viewer-count');
    const status = dialog.querySelector('.js-viewer-status');
    const list = items.map((item) => {
      const thumb = item.querySelector('.js-viewer-thumb');
      return {
        src: item.dataset.viewerSrc || item.href,
        webp: item.dataset.viewerWebp || '',
        thumb,
        alt: thumb ? thumb.alt : '',
      };
    });
    let current = 0;
    let opener = null;

    // 格子の写真が WebP で出ていれば（＝その端末が WebP を表示できる）、大きい画像も WebP にする
    const urlOf = (entry) => (entry.webp && entry.thumb && /\.webp($|\?)/.test(entry.thumb.currentSrc) ? entry.webp : entry.src);
    const at = (i) => list[(i + list.length) % list.length];
    const preload = (i) => {
      const pre = new Image();
      pre.src = urlOf(at(i));
    };

    const show = (i) => {
      current = (i + list.length) % list.length;
      const entry = list[current];
      const url = urlOf(entry);
      if (img.getAttribute('src') !== url) {
        img.classList.add('is-loading');
        img.src = url;
      }
      img.alt = entry.alt;
      cap.textContent = entry.alt;
      count.textContent = `${current + 1} / ${list.length}`;
      // 読み上げは「3枚目（36枚中）：説明」（Tobii・lightGallery の読み上げに倣う）
      status.textContent = `${current + 1}枚目（${list.length}枚中）：${entry.alt}`;
      // 前後の1枚を先に読み込んでおく（送ったときに白く抜けないように）
      preload(current + 1);
      preload(current - 1);
    };
    const reveal = () => img.classList.remove('is-loading');
    img.addEventListener('load', reveal);
    img.addEventListener('error', reveal);

    items.forEach((item, i) => {
      item.addEventListener('click', (e) => {
        // 新しいタブで開く（Cmd／Ctrl／Shift＋クリック・中ボタン）は止めない
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        opener = item;
        // HTML では閉じている間に読まないよう lazy にしてある。開いたら、すぐ読む
        img.loading = 'eager';
        show(i);
        dialog.showModal();
        dialog.querySelector('.js-viewer-close').focus();
      });
    });

    // 閉じたら、いま表示していた写真にフォーカスを戻す（前後に送っていたら、押した写真ではなく最後に見ていた写真。
    // Fancybox・APG の Dialog に倣う）。格子の中で見ていた場所から続けられるように
    dialog.addEventListener('close', () => {
      (items[current] || opener)?.focus();
    });
    dialog.querySelector('.js-viewer-close').addEventListener('click', () => dialog.close());
    dialog.querySelectorAll('.js-viewer-step').forEach((btn) => {
      btn.addEventListener('click', () => show(current + Number(btn.dataset.step)));
    });

    dialog.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight') {
        e.preventDefault();
        show(current + 1);
      } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        show(current - 1);
      }
    });

    // スマホ：左右にスワイプで前後（40px 以上・横の動きが縦より大きいときだけ）。
    // pointer のイベントは、指を横に動かすとブラウザがスクロールの操作として受け取って pointercancel になり、
    // 離したことが届かない（2026-10-01 に実測）。touch のイベントは必ず届くので、こちらで見る。
    // スワイプのあとにクリックが来る端末でも閉じないように、直後のクリックを1回だけ無視する
    let startX = 0;
    let startY = 0;
    let swiped = false;
    dialog.addEventListener('touchstart', (e) => {
      startX = e.changedTouches[0].clientX;
      startY = e.changedTouches[0].clientY;
      swiped = false;
    }, { passive: true });
    dialog.addEventListener('touchend', (e) => {
      const dx = e.changedTouches[0].clientX - startX;
      const dy = e.changedTouches[0].clientY - startY;
      if (Math.abs(dx) >= 40 && Math.abs(dx) > Math.abs(dy)) {
        swiped = true;
        show(current + (dx < 0 ? 1 : -1));
      }
    }, { passive: true });

    // 写真の外（暗い地）を押すと閉じる。ボタン・写真・説明を押したときは閉じない
    dialog.addEventListener('click', (e) => {
      if (swiped) {
        swiped = false;
        return;
      }
      if (e.target === dialog || e.target.classList.contains('js-viewer-outside')) dialog.close();
    });
  };

  /* -----------------------------------------------------------
   * 事業内容ページの読み物（.js-reading-item）：閉じている項目は、行のどこを押しても開く
   * 開閉そのものは details / summary のまま（JS が無くても「続きを読む」で開く。Tab・Enter・Space も summary）。
   * ここは押せる範囲を「続きを読む」の約 90×44px から行全体へ広げるだけ（2026-10-02 新庄さん）。
   * 開いているときは広げない：読んでいる本文を押して閉じてしまわないよう、閉じるのは「閉じる」だけ。
   * 行に乗せたときの指の形と下線は is-armed が付いたときだけ（_reading.scss）＝JS が無いのに押せそうに見せない
   * --------------------------------------------------------- */
  const initReading = () => {
    document.querySelectorAll('.js-reading-item').forEach((item) => {
      const details = item.querySelector('details');
      if (!details) return;
      item.classList.add('is-armed');
      item.addEventListener('click', (e) => {
        if (details.open) return;
        // summary は自分で開く。ここでも開くと二重に切り替わって閉じたままになる
        if (e.target.closest('summary, a, button')) return;
        // 1文目を選んでコピーしようとしているだけのときは開かない
        if (String(window.getSelection())) return;
        details.open = true;
      });
    });
  };

  /* -----------------------------------------------------------
   * 見本のお問い合わせフォーム（contact.html の .js-sample-form）
   * 送信先が決まるまで action="#"。そのまま送るとページが読み直されて入力が消え、
   * 送ったつもりで問い合わせが届かない（2026-10-01 hallmark check P1）。
   * 送信を止めて、送られていないことを送信ボタンのすぐ下で知らせる。
   * 入力の確認はブラウザ標準（required / type=email）に任せる＝不備があると submit まで来ない。
   * WP 版は Contact Form 7 が送るので、このクラスは付けない
   * --------------------------------------------------------- */
  const initSampleForm = () => {
    const form = document.querySelector('.js-sample-form');
    if (!form) return;
    const status = form.querySelector('.js-sample-form-status');

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!status) return;
      // 同じ文を入れ直しても読み上げは2回目を読まないので、いったん空にしてから入れる
      status.textContent = '';
      requestAnimationFrame(() => {
        status.textContent = 'この提案版では送信は行われません。入力した内容は送られていません。';
      });
    });
  };

  const init = () => {
    initHeader();
    initDrawer();
    initAnchor();
    initReveal();
    initCount();
    initStrengthSteps();
    initParallax();
    initSpbar();
    initAreaMap();
    initHistory();
    initFacilityMap();
    initStatsBar();
    initViewer();
    initReading();
    initSampleForm();
  };

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', init, { once: true })
    : init();
})();
