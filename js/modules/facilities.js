// Original facility interaction from jinfoods-lp.
  const initFacilityMap = () => {
    const pins = document.querySelectorAll('.js-facility-pin');
    if (!pins.length) return;

    const detailOf = (pin) => document.getElementById(pin.getAttribute('aria-controls'));
    const isOpen = (pin) => pin.getAttribute('aria-expanded') === 'true';

    // 押して出た詳細を読み上げる欄（u-vh・aria-live）。詳細そのものは hidden の出し入れなので、
    // 入れ物に aria-live を付けるより確実に読まれる（2026-10-02、hallmark check P3）
    const status = document.querySelector('.js-facility-status');
    // 案内「施設名を押すと…」は JS が動いたときだけ出す（JS が無いと詳細は出ないため）
    const hint = document.querySelector('.js-facility-hint');
    if (hint) hint.hidden = false;

    // 読み上げる文：番号（aria-hidden）は外し、「所在地：大分市判田」の形にする
    const speechOf = (detail) => {
      const copy = detail.cloneNode(true);
      copy.querySelectorAll('[aria-hidden="true"]').forEach((el) => el.remove());
      copy.querySelectorAll('dt').forEach((dt) => dt.append('：'));
      copy.querySelectorAll('dd').forEach((dd) => dd.append('。'));
      return copy.textContent.replace(/\s+/g, ' ').trim();
    };

    const setOpen = (pin, open) => {
      pin.setAttribute('aria-expanded', String(open));
      const detail = detailOf(pin);
      if (detail) detail.hidden = !open;
      // 同じ施設を開き直したときも読まれるよう、閉じたら空にして、開くときは次の描画で書く
      if (status && open && detail) requestAnimationFrame(() => { status.textContent = speechOf(detail); });
    };

    const closeAll = () => {
      pins.forEach((pin) => setOpen(pin, false));
      if (status) status.textContent = '';
    };

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
initFacilityMap();
