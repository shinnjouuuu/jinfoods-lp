/* =============================================================
 * FV の写真の層（湯気の層）を 3D にする
 *
 * - 位置・大きさ・濃さの正本は sass/object/project/_fv.scss の表。
 *   ここでは CSS で置いた <img> の画面上の位置と --steam-z（奥行き）を読み、
 *   カメラから見て同じ位置・同じ大きさになるように 3D に置く（止まった 3D ＝ CSS の見た目）
 * - Three.js r128 と GSAP 3.12（＋ScrollTrigger）は cdnjs から、ページの読み込みが終わって
 *   手が空いたときに読む（見出しの表示を遅らせない）。SRI は cdnjs の公開値
 * - 動くのは、読み込み時に下から立ちのぼる1回（約2.5秒）、マウスに合わせた奥行きの差、
 *   スクロールで上へ抜けるときだけ。何もしなければ止まり、描き直しもしない
 * - WebGL が無い・読み込みに失敗した・5秒で出なかったときは、CSS の写真を出す
 *   （HTML 直後の小さな script が、試すときだけ is-pending を付けている）
 * ========================================================== */
(() => {
  'use strict';

  const layer = document.querySelector('.js-fv-steam');
  // 動きを減らす設定・通信を節約する設定では is-pending が付かない（CSS の写真のまま）
  if (!layer || !layer.classList.contains('is-pending')) return;

  const fv = layer.closest('.js-fv');
  const imgs = [...layer.querySelectorAll('.js-fv-steam-img')];
  let built = false;
  let aborted = false;
  const giveUp = () => {
    aborted = true;
    layer.classList.remove('is-pending');
  };

  const hasWebGL = () => {
    try {
      const c = document.createElement('canvas');
      return !!(window.WebGLRenderingContext && (c.getContext('webgl') || c.getContext('experimental-webgl')));
    } catch (e) {
      return false;
    }
  };
  if (!hasWebGL()) {
    giveUp();
    return;
  }

  const LIBS = [
    ['https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', 'sha512-dLxUelApnYxpLt6K2iomGngnHO83iUvZytA3YjDUCjT0HDOHKXnVYdf3hU4JjM8uEhxf9nD1/ey98U3t2vZ0qQ=='],
    ['https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', 'sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg=='],
    ['https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', 'sha512-onMTRKJBKz8M1TnqqDuGBlowlH0ohFzMXYRNebz+yOcc5TQr/zAKsthzhuv0hiyUKEiQEQXEynnXCvNTOk50dg=='],
  ];
  const loadScript = ([src, integrity]) => new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = src;
    s.integrity = integrity;
    s.crossOrigin = 'anonymous';
    s.referrerPolicy = 'no-referrer';
    s.onload = resolve;
    s.onerror = reject;
    document.head.appendChild(s);
  });
  const isShown = img => getComputedStyle(img).display !== 'none';
  const whenLoaded = img => (img.complete && img.naturalWidth
    ? Promise.resolve()
    : new Promise((resolve, reject) => {
      img.addEventListener('load', resolve, { once: true });
      img.addEventListener('error', reject, { once: true });
    }));

  // 3D と CSS で同じにしておく値（_fv.scss の $_cam-z・$_fog-near・$_fog-far・$_steam-radius と対）
  const CAM_Z = 10;          // カメラの位置（写真の奥行き z はこれより手前の 1 まで）
  const FOG_NEAR = 9;        // 霧がかかり始める距離
  const FOG_FAR = 21;        // 霧で生成りに消える距離
  const RADIUS = 22;         // 角丸（256px の型に対して。22 / 256 ≒ 8.6%）
  const DEEPEST_Z = -9;      // 表のいちばん奥の写真。スクロールで上がる速さの基準
  const RISE_FROM = -1.4;    // 立ちのぼりの始まり（写真1枚ぶん強、下から）
  const HOVER = 1.2;         // マウスを乗せた写真の拡大率（_fv.scss の $_steam-hover と対）
  // マウスを乗せた写真を運ぶ奥行き。霧がかかり始める位置（CAM_Z − FOG_NEAR ＝ 1）と表のいちばん手前（1）より前なので、
  // 霧が晴れて元の写真の色になり、どの写真よりも手前に来る
  const FRONT_Z = CAM_Z - FOG_NEAR + 0.5;

  // 3D の写真の角丸
  const makeAlpha = THREE => {
    const c = document.createElement('canvas');
    c.width = c.height = 256;
    const g = c.getContext('2d');
    const r = RADIUS;
    g.fillStyle = '#000';
    g.fillRect(0, 0, 256, 256);
    g.fillStyle = '#fff';
    g.beginPath();
    g.moveTo(r, 0);
    g.arcTo(256, 0, 256, 256, r);
    g.arcTo(256, 256, 0, 256, r);
    g.arcTo(0, 256, 0, 0, r);
    g.arcTo(0, 0, 256, 0, r);
    g.closePath();
    g.fill();
    return new THREE.CanvasTexture(c);
  };

  const build = () => {
    // CSS の保険（6秒）がもう写真を出していたら、3D には切り替えない（写真が一度消えて出直すのを防ぐ）
    const first = imgs.find(isShown);
    if (aborted || (first && getComputedStyle(first).visibility === 'visible')) {
      giveUp();
      return;
    }
    const { THREE, gsap, ScrollTrigger } = window;
    gsap.registerPlugin(ScrollTrigger);

    const FOV = 35;
    const TAN = Math.tan((FOV / 2) * Math.PI / 180);
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.outputEncoding = THREE.sRGBEncoding;
    renderer.domElement.className = 'p-fv__steam-canvas';
    const scene = new THREE.Scene();
    // 遠い写真ほど生成りにかすむ（霧の色は --color-paper）
    const paper = getComputedStyle(document.documentElement).getPropertyValue('--color-paper').trim();
    if (paper) scene.fog = new THREE.Fog(new THREE.Color(paper), FOG_NEAR, FOG_FAR);
    const camera = new THREE.PerspectiveCamera(FOV, 1, 0.1, 100);
    const alpha = makeAlpha(THREE);

    let dirty = true;
    let onScreen = true;
    let lost = false;
    const mark = () => { dirty = true; };
    const state = { scroll: 0 };

    const items = imgs.map(img => {
      const tex = new THREE.Texture(img);
      tex.encoding = THREE.sRGBEncoding;
      const mat = new THREE.MeshBasicMaterial({ map: tex, alphaMap: alpha, transparent: true, opacity: 0 });
      const mesh = new THREE.Mesh(new THREE.PlaneGeometry(1, 1), mat);
      mesh.visible = false;
      scene.add(mesh);
      return { img, tex, mat, mesh, base: null, size: 1, hv: 1, fz: 0, loaded: false, dy: RISE_FROM, op: 0 };
    });

    // 1枚の位置：CSS の位置＋立ちのぼり（dy）＋スクロール。奥の写真ほどゆっくり上がる
    // fz（0〜1）で FRONT_Z へ運ぶ。手前に寄せると遠近で大きく・中心から離れて見えるので、
    // k を掛けて画面上の位置と大きさを元のままにする（大きさは hv だけで変わる）
    const apply = it => {
      if (!it.base) return;
      const lift = state.scroll * (1.2 + (it.base.z - DEEPEST_Z) * 0.25);
      const z = it.base.z + (FRONT_Z - it.base.z) * it.fz;
      const k = (CAM_Z - z) / (CAM_Z - it.base.z);
      it.mesh.position.set(it.base.x * k, (it.base.y + it.dy + lift) * k, z);
      it.mesh.scale.set(it.size * it.hv * k, it.size * it.hv * k, 1);
      it.mat.opacity = it.op * (1 - state.scroll * 0.8);
    };

    // CSS の <img> の位置と大きさを、奥行き z の平面に写す
    const layout = () => {
      const w = layer.clientWidth;
      const h = layer.clientHeight;
      if (!w || !h) return;
      renderer.setSize(w, h, false);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      const box = layer.getBoundingClientRect();
      items.forEach(it => {
        if (!isShown(it.img) || !it.loaded) {
          it.mesh.visible = false;
          it.base = null;
          return;
        }
        const r = it.img.getBoundingClientRect();
        const z = parseFloat(getComputedStyle(it.img).getPropertyValue('--steam-z')) || 0;
        const pxPerUnit = h / (2 * (CAM_Z - z) * TAN);
        it.base = {
          x: (r.left + r.width / 2 - box.left - w / 2) / pxPerUnit,
          y: -(r.top + r.height / 2 - box.top - h / 2) / pxPerUnit,
          z,
        };
        it.size = r.height / pxPerUnit;
        it.mesh.visible = true;
        apply(it);
      });
      mark();
    };

    // 画面幅が変わって新しく出た写真は、読み込み終わってから 3D に足す
    const loadShown = () => items.forEach(it => {
      if (it.loaded || !isShown(it.img)) return;
      whenLoaded(it.img).then(() => {
        it.loaded = true;
        it.tex.needsUpdate = true;
        layout();
      }).catch(() => {});
    });

    const cam = { x: 0, y: 0 };
    const applyCamera = () => {
      camera.position.set(cam.x, cam.y, CAM_Z);
      camera.lookAt(0, 0, 0);
      mark();
    };
    applyCamera();

    items.forEach(it => {
      if (isShown(it.img) && it.img.complete && it.img.naturalWidth) {
        it.loaded = true;
        it.tex.needsUpdate = true;
      }
    });
    layout();
    new ResizeObserver(() => { loadShown(); layout(); }).observe(layer);
    new IntersectionObserver(([e]) => { onScreen = e.isIntersecting; if (onScreen) mark(); }).observe(fv);
    gsap.ticker.add(() => {
      if (dirty && onScreen && !lost) {
        renderer.render(scene, camera);
        dirty = false;
      }
    });

    // ここまで来たら CSS の写真と入れ替える
    layer.appendChild(renderer.domElement);
    layer.classList.remove('is-pending');
    layer.classList.add('is-3d');
    built = true;
    renderer.domElement.addEventListener('webglcontextlost', () => {
      lost = true;
      layer.classList.remove('is-3d');
      renderer.domElement.remove();
    });

    // 読み込み時に1回だけ、下から立ちのぼる
    items.filter(it => it.base).forEach((it, i) => {
      gsap.to(it, {
        dy: 0,
        op: 1,
        duration: 1.6,
        delay: 0.1 + i * 0.1,
        ease: 'power2.out',
        onUpdate: () => { apply(it); mark(); },
      });
    });
    // 画面幅が変わった後に足した写真は、立ちのぼらせずにそのまま出す
    items.forEach(it => {
      if (!it.base) {
        it.dy = 0;
        it.op = 1;
      }
    });

    // マウスに合わせてカメラを少し動かす（手前の写真ほど大きく動き、奥行きの差が出る）
    if (window.matchMedia('(hover: hover)').matches) {
      // マウスの下にある写真を探して HOVER 倍にする（キャンバスはクリックを通すので、FV で位置を拾って当たりを計算する）
      const ray = new THREE.Raycaster();
      const ndc = new THREE.Vector2();
      let hot = null;
      const setHot = next => {
        if (next === hot) return;
        const prev = hot;
        hot = next;
        if (prev) {
          prev.mesh.renderOrder = 0;
          gsap.to(prev, { hv: 1, fz: 0, duration: 0.35, ease: 'power2.out', overwrite: 'auto', onUpdate: () => { apply(prev); mark(); } });
        }
        if (next) {
          next.mesh.renderOrder = 1;  // 拡大した写真を隣の写真より手前に描く
          gsap.to(next, { hv: HOVER, fz: 1, duration: 0.35, ease: 'power2.out', overwrite: 'auto', onUpdate: () => { apply(next); mark(); } });
        }
      };
      const pick = e => {
        const r = layer.getBoundingClientRect();
        ndc.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
        ray.setFromCamera(ndc, camera);
        const hit = ray.intersectObjects(items.filter(it => it.base).map(it => it.mesh))[0];
        return hit ? items.find(it => it.mesh === hit.object) : null;
      };

      fv.addEventListener('pointermove', e => {
        const r = fv.getBoundingClientRect();
        const dx = (e.clientX - r.left) / r.width - 0.5;
        const dy = (e.clientY - r.top) / r.height - 0.5;
        gsap.to(cam, { x: dx * 1.0, y: -dy * 0.7, duration: 1.3, ease: 'power2.out', overwrite: 'auto', onUpdate: applyCamera });
        setHot(pick(e));
      });
      fv.addEventListener('pointerleave', () => {
        gsap.to(cam, { x: 0, y: 0, duration: 1.4, ease: 'power2.out', overwrite: 'auto', onUpdate: applyCamera });
        setHot(null);
      });
    }

    // スクロールで、写真が上へ抜けながら薄くなる（湯気が立ちのぼる向き）
    ScrollTrigger.create({
      trigger: fv,
      start: 'top top',
      end: 'bottom top',
      scrub: 0.5,
      onUpdate: self => {
        state.scroll = self.progress;
        items.forEach(apply);
        mark();
      },
    });
  };

  const start = () => {
    const shown = imgs.filter(isShown);
    Promise.all([
      LIBS.reduce((p, lib) => p.then(() => loadScript(lib)), Promise.resolve()),
      ...shown.map(whenLoaded),
    ]).then(build).catch(giveUp);
    // 5秒で出なければあきらめて CSS の写真を出す（CSS 側も $_steam-failsafe の6秒で出す。
    // 数え始めはこちらのほうが遅いので、build() の頭で CSS が先に出していないかを確かめている）
    setTimeout(() => { if (!built) giveUp(); }, 5000);
  };
  const kick = () => ('requestIdleCallback' in window
    ? window.requestIdleCallback(start, { timeout: 1500 })
    : setTimeout(start, 200));
  document.readyState === 'complete' ? kick() : window.addEventListener('load', kick, { once: true });
})();
