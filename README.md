# jinfoods-lp

株式会社ＪＩＮｆｏｏｄｓ（大分県／給食委託・厨房業務）の1枚もの LP。

- **目的**：委託の問い合わせ獲得（主）＋ 採用応募（副）
- **ターゲット**：① 高齢者施設・病院の運営者／施設長　② 調理スタッフ・栄養士の求職者
- **公開先の想定**：現行サイト（https://jin-oita.com/）のトップ差し替え

---

## 起動

```bash
npm install
npm run dev
```

`npm run dev` は Sass のウォッチのみ。プレビューは `~/.claude/launch.json` に
`jinfoods-lp`（port **8494**）を登録済みなので、そこから起動する。

| コマンド | 内容 |
|---|---|
| `npm run dev` | Sass ウォッチ（expanded・sourcemapなし） |
| `npm run build` | 本番用にコンパイル（compressed） |
| `npm run build:dev` | 1回だけコンパイル（expanded） |

**`css/style.css` は生成物。直接編集しない**（規約）。触るのは `sass/` 配下だけ。

**Git（2026-10-02〜）は手元だけ。push・リモートの追加はしない。**
- commit は新庄さんの了承を得てから。ほかの会話の作業途中を、その会話に断らずに commit しない
- 本番に出したら、出した中身の commit に `prod-YYYY-MM-DD-HHMM` のタグを付ける（最初は `prod-2026-10-02-1131`＝jinfoods-3z0e2jxpk）

---

## ファイル構成

```
jinfoods-lp/
├── index.html
├── css/style.css          ← 生成物。編集しない
├── js/main.js
├── images/                ← 仮画像（SVG）。差し替え表は後述
└── sass/
    ├── style.scss         ← 読み込み順を固定するエントリ
    ├── foundation/        _variables（トークン・fluid・mq-up）/ _reset / _base
    ├── layout/            _inner / _header / _section / _footer
    └── object/
        ├── component/     _btn / _heading / _card / _stat / _tag / _media
        ├── project/       _fv / _about / _stats / _facility / _strength /
        │                  _service / _gallery / _news / _recruit / _contact / _spbar
        └── utility/       _utility
```

---

## 設計ルール（このプロジェクトで守っていること）

- **BEM + FLOCSS**。読み込み順は Foundation → Layout → Object(C → P → U) で固定
- **見た目と動きのクラスを分離**。JS が触るのは `js-` のみ。`l-` / `c-` / `p-` を
  セレクタに使わないので、デザイン変更で JS が壊れない
- 状態は `is-` / `aria-*`。見た目の指定は CSS 側が持ち、JS は付け外しだけ
- **`c-` は margin / width を持たない**。幅が必要なときは Utility の `.u-full` を併記する
- 余白は **gap 優先**。`margin` で積み上げない
- **`max-width` と左右 padding は `.l-inner` に集約**。子に固定幅をばらまかない
- 色・余白・BP は全部 `_variables.scss` のトークン経由。直書きなし
- **モバイルファースト**。分岐はふだん `mq-up()`。「ある幅まで」に限りたいときだけ `mq-down()`（`mq-up` の裏返しで指定値を含むので、同じキーで重ならない。2026-10-02 追加、いまはギャラリーの締めのマスだけ）
- 流動サイズは `fluid($min, $max)` で `clamp()` を生成。引数は px 統一
- タグセレクタと id セレクタは Foundation 層だけ

### 数値仕様

| 項目 | 値 |
|---|---|
| PC コンテンツ幅 | **1140px**（実測で一致） |
| PC 左右の内側余白 | **2rem** |
| 768px 以下の左右余白 | **1rem** |
| 最小サポート幅 | **375px** |
| BP | `sm 375` / `md 768` / `nav 1000` / `lg 1140` |

`nav 1000` を `md 768` と別に切っている理由：ヘッダーはナビ5項目＋電話＋CTA2つを
持つので 768px ではロゴと同居できない。ヘッダー・ハンバーガー・ドロワー・SP固定バーを
まとめて `nav` で切り替える。バラバラに切ると 768〜999px で「ナビもCTAも無い」穴ができる。

---

## アニメーション

参考サイトの実装を分析して、**挙動だけを設計パターンとして書き起こしたもの**。
コードの流用はしていない。外部ライブラリは FV の写真の層の Three.js r128・GSAP 3.12.5（cdnjs から読み込み後に読む）だけ。

| 箇所 | 挙動 | フック |
|---|---|---|
| **FV（縦組み）** | 読み込み時に1回だけ、右の列から順に上から下へ出す（受託の一文 → 見出し → ボタン、180ms ずつ）。**CSSの animation だけ**。終わったら切り抜きを外す（`backwards`） | `.p-fv__offer` `.p-fv__title` `.p-fv__cta`（`js-fv` は SP 固定バーと写真の層の判定に使う） |
| **FV（写真の層）** | 読み込み時に下から立ちのぼる（1回）・マウスで奥行きの差・スクロールで上へ抜ける。Three.js＋GSAP（`js/fv-steam.js`）。使えないときは CSS の止まった写真 | `js-fv-steam`（状態 `is-pending` → `is-3d`） |
| **強み（食事形態の5段階）** | 見えたら左から順に出る（1回だけ・0.14s ずつ）。2026-09-30 に紙芝居（sticky の札送り）をやめた（節が 3.3 画面分あり、SP で進み具合が固定バーの裏に隠れていた） | `js-strength-steps`（状態 `is-armed` → `is-drawn`） |
| 各セクション | 下から16px＋フェードイン。80ms ずつ遅延 | `js-reveal` → `.u-reveal.is-inview` |
| 数字 | 画面に入ったらカウントアップ（900ms・終盤減速） | `js-count` + `data-count-to` |
| 数字セクション背景 | 弱いパララックス（**適用はここ1箇所だけ**） | `js-parallax` |
| ヘッダー | スクロールで影を出す（番兵を監視。scroll ハンドラを使わない） | `js-header` |
| SP固定バー | FV を抜けてから出す（FV内にCTAが2本あるので重複させない） | `js-spbar` |
| ドロワー | `hidden` も付け外し。**ヘッダーはドロワーより前面**（`--z-header: 9100` > `--z-drawer: 9000`）にして、開いている間も×（ハンバーガーが変形）を押せるようにしてある | `js-drawer` `js-drawer-toggle` `js-drawer-label` |

### 動きは progressive enhancement

JS が付ける状態（`is-armed` など）が無いときに、**中身が1つも見えない状態を作らない**。
強みの5段階も、JS が落ちたときや `prefers-reduced-motion` のときは最初から全部出ている。

`prefers-reduced-motion: reduce` で全部止める。止めたときに要素が消えたままにならないよう、
`.u-reveal` は reduce 時に最初から表示する。

**慣性スクロールは採用していない。** 読み手が施設の運営者（40〜60代）と求職者なので、
「思った位置で止まらない」挙動は不利になる。参考にした spc-jpn.co.jp は luxy.js で
慣性＋パララックスをかけているが、この案件では外した。

---

## 掲載している数値の出典

すべて公開情報から検証済み。**推測値は1つも入れていない。**

| 項目 | 値 | 出典 |
|---|---|---|
| 受託施設数 | 8施設（6契約先） | 業務案内ページの受託契約施設リスト |
| うち病院 | 1施設（清瀬病院・別府市／40床） | 同リスト＋病院公式 |
| スタッフ数 | 50名 | 会社概要 |
| 有資格者 | 栄養士・管理栄養士・調理師 | 会社概要 |
| 稼働時間 | 6:00〜19:00／定休日なし | 会社概要 |
| 住所・TEL・FAX | 大分市大道町5丁目3-32／097-574-9225／097-574-9235 | 会社概要 |
| 法人名 | 株式会社ＪＩＮｆｏｏｄｓ | gBizINFO 法人番号 7320001018192 |
| お知らせ3件 | 2025.04.01／2025.02.28／2025.01.31 | WordPress の実データ |

**モックにあった以下の数字は使っていない**（実態と矛盾するため）：
年間250万食／導入120施設／創業25年／スタッフ80名／東京丸の内の住所／03・0120の番号。

### 要確認（HTML内にコメントで残してある）

1. **スタッフ50名の時点** — 会社概要に「従業員数50名」とあるが何年何月時点かの記載がない
2. **受託施設名の掲載許諾** — 業務案内ページで既に公開済みだが、トップで前面に出すと
   露出度が変わる。特に清瀬病院は医療法人なので確認が望ましい
3. **年間提供食数** — 公表がない。出す場合は根拠を確認（8施設規模なら年40〜70万食が目安）
4. **企業・社員食堂** — 受託実績ゼロ。実績ではなく「対応可能領域」として別枠に落としてある
5. **お知らせの更新運用** — 実データの最終更新が 2025/04/01。運用を決めないと
   新デザインでも1年以上前のお知らせが最上部に出る（デザインでは解決しない）

### 採用セクションに労働条件を書いていない理由

既存の採用ページに**受動喫煙防止措置の記載がなく**（職業安定法の募集時明示事項）、
雇用形態・パートの契約期間・「205,000円＋諸手当」の内訳も未記載。社労士確認が済むまで
新しい面に転記すると不足を増やすだけなので、このLPは
**「募集職種と働き方の概要 → 詳細は採用情報ページへ」** に留めている。

---

## 画像の書き出し（軽量化・2026-10-01）

**`images/` を直接編集しない。** 元画像は `images-src/`（配信しない・`.vercelignore` 済み）に置き、次で書き出す：

```bash
python3 images-src/optimize.py
```

- 同じファイル名の JPEG のまま、**表示される最大の幅（CSS px）× 2** に縮めて圧縮し直す（幅の表はスクリプトの `RULES`）。
  WordPress テーマの `assets/images/` に同じ名前があれば、そちらも同じものに置き換える
- 食事の写真のタイル（`g-*` `gallery-*` `menu-*`）は WebP も書き出し、HTML は `<picture><source type="image/webp">` で出す。
  `<img>` は JPEG のまま（WebP を表示できない古い端末向け）。WP テーマに WebP を置くのは `menu-*` だけ
  （固定ページ「事業内容」の本文がテーマの WebP を直接指すため。無いと 404 で画像が出ない。2026-10-01）
- 下層のページ見出しの写真（各ページの LCP）は `fetchpriority="high"`。画面の外にある写真は `loading="lazy"`
- 表示の大きさを変えたら、`RULES` の幅も直してから流し直す
- 食事ギャラリーのページの格子だけは、小さい版 `g-XX-s.jpg／.webp`（400px）を別に書き出す（スクリプトの `THUMBS`）。`g-XX` は拡大表示とお知らせの記事に使う 800px（2026-10-01、C案）
- 結果（ページを最後までスクロールしたときの画像の合計）：トップ 2.5MB→1.4MB、食事ギャラリー 4.5MB→1.7MB、事業内容 1.2MB→0.7MB、会社概要 411KB→210KB

## 書体の読み込み（軽量化・2026-10-01）

見出しの Zen角ゴシック 700 と英数字の Jost は、Google Fonts に「このサイトで使う字だけ」を頼んでいる（`&text=`）。
各ページの `<head>` の `<!-- fonts:start -->`〜`<!-- fonts:end -->` は次のスクリプトが書く。手で直さない：

```bash
node fonts-src/make-font-link.mjs
```

- 12ページを Chrome（ヘッドレス）で開き、Zen角ゴ・Jost で描かれる字を集める。見えていない要素（ドロワー・ホバーで出る説明など）と `::before`／`::after` の文字も拾う
- **見出し・ボタン・ラベルなど、この2つの書体を使う所の文言を足したり変えたりしたら流し直す**。流さないと、新しい字だけ端末の書体で出る
- 描画は止めない（preload → 届いたら stylesheet）。WP は投稿で字が増えるので text= を付けず、描画を止めない読み込みだけ（`inc/enqueue.php`）
- 結果：書体ファイル 28本・273KB → 2〜3本・57KB。遅い4G（1.6Mbps・150ms）で FCP 1.12秒→0.99秒、書体がそろうまで 3.6秒→2.6秒
- 2026-10-01 夜：リポジトリの中で流すと、Chrome がページを読む途中で止まって終わらなかった。そのときは写しのフォルダで流し、各ページの `fonts:start`〜`fonts:end` の部分だけを本物に当てる

## 計測の目印（`data-cta`・2026-10-01）

どのボタンが押されたかを分けて数えるための印。問い合わせ・電話・応募のボタンに `data-cta="何-どこ"` を付けている。
見た目・CSS・JS には何も効かない。GA4／GTM の設定が決まったら、クリックの条件にこの属性を使う（いまは計測のタグ自体が無い）。

| 何 | 意味 |
| --- | --- |
| `tel-` | 電話をかける（tel: リンク） |
| `contact-` | お問い合わせページへ |
| `trial-`／`consult-` | 締めの帯の入口「試食のご依頼」／「業務のご相談」（どちらも contact.html#form） |
| `recruit-` | 採用ページへ（募集要項を見る・採用情報・採用について） |
| `apply-tel-`／`apply-form-` | 採用ページでの応募（電話／フォーム） |

| どこ | 場所 |
| --- | --- |
| `-fv` | トップの最初の画面の「お問い合わせ」（ページ内の締めの帯へ飛ぶ） |
| `-header` | ヘッダー（PC）の電話番号・お問い合わせ・採用情報 |
| `-drawer` | ドロワー（SP のメニュー）の電話・お問い合わせ・採用の帯 |
| `-spbar` | SP の下に固定のバー（採用ページだけ `apply-…-spbar`） |
| `-footer` | フッターの電話番号 |
| `-band` | 各ページの最後の締めの帯（`p-contact`。採用ページだけ `apply-…-band`） |
| `-top` | トップの節の中：採用の「募集要項を見る」（`recruit-top`）、事業内容の「企業・社員食堂もご相談ください」の行（`consult-top`） |
| `-recruit` | 採用ページの条件のすぐ下の電話・フォーム |
| `-inquiry` | お問い合わせページ（電話の案内・フォームの中の「募集要項を見る」） |
| `-company`／`-access` | 会社概要の表の電話／アクセスの電話 |
| `-service` | 事業内容ページの「相談する」 |

- 目次（ヘッダーのナビ・ドロワーの目次タイル・フッターの目次）には付けない。入口ではなく道案内なので
- 付けている所：LP は11ページで142か所（2026-10-01 夜に、トップのページ内リンク `#contact` の2か所をあとから足した）。WP は header.php・footer.php・front-page.php・template-parts/cta.php と、固定ページの本文（content/ の about・service・recruit・contact）。属性は `class` のすぐ後ろに置く（export-content.py で LP から書き出したときに、content/ と同じ形になるように）
- 新しいボタンを足したら、同じ「何-どこ」の決まりで付け、この表に無い場所なら表に足す。別ページへのリンクと電話だけでなく、ページ内の締めの帯へ飛ぶリンク（`href="#contact"`）も入口として数える
- 先方の答えしだいで消えるもの：`trial-*`（追補15「試食を受けているか」）、`consult-top`（追補16「社員食堂の相談に乗れるか」）。ボタンや行ごと消すときは、この表も直す
- まだ付けていない：フォームの送信ボタン、WP のお問い合わせフォームの中の「募集要項を見る」（Contact Form 7 に移す作業の途中のため。`wp-theme/cf7/contact-form.html`）。送信の件数は、Contact Form 7 の送信完了で数えるほうが確か

## 画像：実写の棚卸し結果

現行サイトの WP REST（`?rest_route=/wp/v2/media`）で**全185件**を棚卸しした。

| 解像度 | 件数 |
|---|---|
| 2000px以上 | **99件** |
| 1200〜1999px | 5件 |
| 800〜1199px | 27件 |
| 500〜799px | 10件 |
| 500px未満 | 36件 |

**結論：料理・行事食・介護食の実写は潤沢（多くが 2560×1920 のiPhone写真）。厨房・スタッフ・提供シーンの実写はゼロ。**

このためFVは「人物」ではなく**「料理そのもの」で勝負する**設計に変えた。
ストック素材の人物1枚より、本物の行事食を同時に4枚見せたほうが給食委託の説得力が出る。

### 使えなかったもの

- **人物写真（`094`）** … 現行サイトの業務案内ページ見出し。**画像内に文字が焼き込まれていて**
  （「業務案内・・・Business guide」「㈱JIN foods は各施設にて介護食の提供を行っています」）、
  かつ明らかにストック素材で実際のスタッフではない。使わない
- 同様に「お問い合わせ」「プライバシーポリシー」「サイトマップ」の見出し画像もストック素材

### いま入っている画像

**仮画像はなくなった**（ロゴは 2026-09-30 に実データへ差し替え。`ogp.png` は未作成）。

| ファイル | 出所 | 内容 |
|---|---|---|
| `strength-1` | 生成 | 管理栄養士による検食 |
| `strength-4` | 生成 | 厨房スタッフと施設職員の打ち合わせ |
| `about-main` | 生成 | 厨房で野菜を刻むスタッフ2名 |
| `recruit` | 生成 | 厨房で働くスタッフ |
| `stats-bg` | 生成 | 厨房の引き（14%） |
| `strength-2` `strength-3` | 実写 | ムース食／彩り豊かな行事食 |

`kv-*` 12枚と `contact-bg` は 2026-10-02 に、どこでも使っていないので消した（下の「使っていない CSS・画像の削除」）。
| `service-1` `service-2` | 実写 | 高齢者施設の献立／焼き魚の定食 |
| `about-sub` | 実写 | 配膳されたトレー |
| `gallery-1〜8` | 実写 | 行事食・デザート・フルーツ |
| `g-17〜36`（gallery.html） | 実写・追加 | 配膳トレー・行事食・大皿。20枚足して計36枚（2・3・4列のどれでも最終行が欠けない） |
| `menu-1〜4`（service.html「実際にお出ししている食事」） | 実写・追加 | 配膳トレー・行事膳 |
| `logo.png` | 実データ（**暫定の画質**） | 現行サイトの横組みロゴ `images/mainerea_logo.png`（366×177、写真に載せる用の白いぼかし入り）から、白だけを透明にして切り出した 282×106。表示は最大160px幅なので約1.8倍密度。ベクター（ai / svg）の原本が届いたら差し替える。縦組み（`h_logo.jpg` 110×128）はヘッダー幅だと社名が8px前後になり読めないので使っていない。旧 `logo.svg` は仮画像で、どこからも参照していない |
| `ogp.png` | **未作成** | 中央630×630に社名とコピー |

**FVは生成、ギャラリーと強み②③は実写**という切り分け。
実写はFVに一度当てたが、デザート中心の記録写真で「幼稚園のおやつ」に寄って見えたため差し戻した。

### 追加の実写（2026-09-30）

- 出所：`~/Desktop/JINfoods 画像/`（55枚）。うち49枚は 2026-08-15 に共有された LINE アルバム「O.C.K様」＝関連会社 合同会社O.C.K とのアルバム。LINE を通したので EXIF（撮影日時・機種）は残っていない
- 使ったのは**配膳トレーと行事食だけ**（24枚）。キャラクターのおやつ・人物が写るもの・食堂の引きは外した。1:1 の 800×800 に中央で切り出し、既存と同じ1枚100KB前後
- **施設か病院かは未確認**なので、事業内容の2領域（高齢者施設／病院）のカードには入れず、サービスページは独立した段にした
- **厨房6枚は保留**。JINfoods の施設厨房か O.C.K のセントラルキッチン（真空パック＝完調品）か、写っているスタッフの了承、の2点が先方確認待ち。答えが来たら about-main / recruit / 強み①④ の生成画像と差し替える候補

### FVの写真の層（湯気の層・2026-09-30 G案）

縦組みはそのまま、左右に**実写の料理・行事食**を奥行きの違う層で浮かべる。写真の置き方7案（CSS 3案＋GSAP・Three.js 4案）を動くモックで比べて G を採用：
https://claude.ai/artifact/6Vo6Kpi6poxWkEHo9BpwNz

- 写真：`images/fv/fv-01〜09.jpg`（実写 g-23, g-29, g-19, g-31, menu-4, g-17, g-22, g-20, g-34 を 400px 角に縮めたもの・合計約338KB）。lg〜 9枚／md〜lg 6枚／md 未満 6枚（md 未満と md〜lg は先頭6枚）
- 位置・大きさ・濃さの正本は `_fv.scss` の3つの表。縦組みとボタンには重ねない（320〜1920px の13幅で重なり0を確認）
- `js/fv-steam.js` が、CSS の `<img>` の位置と `--steam-z`（奥行き）を読み、カメラから見て同じ位置・大きさになるよう Three.js で 3D に置く。止まった 3D は CSS の見た目と一致する
- 大きさ：表の値 × 1.75（2026-09-30「もうちょっと大きく」→「ボタンとはかぶっていい」）。層の幅の 22% が上限（375px 未満でボタンが2段になり FV が高くなると、写真が縦組みに近づきすぎるため）。PC 1440 で 165〜284px。縦組みの文字とは、止まった状態でもホバーで1.2倍にしても重ならない（13幅＋PC 8幅で実測）
- 動き：読み込み時に下から立ちのぼる（約2.5秒・1回）／マウスで奥行きの差、乗せた写真は1.2倍（hover できる端末だけ。3D はレイキャスト、CSS は :hover）／スクロールで上へ抜けながら薄くなる。放っておけば止まり、FV が画面外なら描かない
- ライブラリ：Three.js r128・GSAP 3.12.5・ScrollTrigger を cdnjs から、ページの読み込みが終わって手が空いたときに読む（SRI は cdnjs の公開値。転送は約159KB）。見出しの表示（LCP 0.06〜0.12秒）は遅れない
- 3D にしないとき：動きを減らす設定・通信を節約する設定（HTML 直後の小さな script が `is-pending` を付けない）、WebGL が無い、cdnjs の読み込みに失敗した、5秒で出なかった → CSS の写真がそのまま出る。JS が止まっても CSS が6秒で写真を出す
- WP 版：`front-page.php` に同じ写真の層、`inc/enqueue.php` がトップだけ `assets/js/fv-steam.js` を読む

### FVは縦組みの文字だけ（2026-09-30 作り直し・2回目）

縦組みで、右から 受託の一文 → 見出し → ボタン2つ。**写真は置かない。**
A案（写真4枚の帯）を本番に出したあと、別方向の3案（縦組み／大分の地図／用件の入口）を動くモックで比べて1案を採用した：
https://claude.ai/artifact/51wTD4bRXNQ2Ru8Gvmtoa9

- 理由：A案の写真4枚は4枚とも生成画像で、PCでは画面の右半分（714px）を占めていた。実写が届くまで、最初の画面に生成画像を出さない
- 見出しのかぎかっこ（「「食」で、」）は外した。縦にすると位置が書体しだいでずれるため（09-30 の指示）。同日、サイト全体のかぎかっこも外した（OGP画像を書き出し直し・会社概要のリード文・事業内容の理念の見出しと本文・WP版の同じ箇所）
- 縦組みの列の長さは `fluid(352px, 576px)` に固定している。決めないと列が途中で折れる（モックで見出しが5列に割れた）
- 横並び（右に縦組み・左にボタン）は md（768.02px）から。それより狭いとボタンは縦組みの下に横組みで出る

| 実測 | A案（写真4枚の帯） | 1案（縦組み） |
|---|---|---|
| FVで読み込む写真 | 4枚・344KB（すべて生成） | 0枚 |
| 見出し | 横組み 56px（SP 30px）「「食」で、」 | 縦組み 60px（SP 36px）「食で、」 |
| 受託の一文 | 横組み PC 19px／SP 13px | 縦組み PC 20px／SP 13px（1列） |
| FVの高さ | PC 736px／SP 463px | PC 736px／SP 536px |
| ボタンの下端（SP 375×667） | 435px | 508px |
| LCP | PC は見出し。SP は次の節の写真（約0.9秒） | 見出し（0.04〜0.06秒）。768px 幅だけ次の節の写真 |
| 動き | 写真が左から1回 | 列ごとに上から1回 |

#### （旧）A案：写真4枚の帯（2026-09-30 午前〜午後）

受託の一文 → 見出し → ボタン2つ＋シーン①の4枚を「1→4」の帯で並べた。前（4パネル×3シーン・12枚・989KB・5.6秒自動切替）からの作り直しで、
比較は https://claude.ai/artifact/6REfWgXCrv84Pkva7bUXLH 。前の形は同業大手（富士産業）のFVとほぼ同じだった。

- ABOUT の写真（`about-main.jpg`）は `loading="lazy"` を外したまま。1案でもSP・タブレットの最初の画面に入る

### 生成画像で気をつけたこと

- **画像に文字を一切描かせない**。プロンプト末尾で kanji / hiragana / signage / menus /
  digital displays を全部禁止した。前のモックの「採用惰報」「2226-83」はこれをやらなかった結果
- **`Not overhead flat-lay under fluorescent light`** を必須指定にした。
  実写の弱点（真上・蛍光灯・記録写真）を生成側で繰り返させないため
- 強み①は当初「献立表を見る」で書いていたが、**紙に文字を描かせると必ず崩れる**ので
  **検食（提供前の1食確認）** に変えた。給食施設が実際に行う業務なので内容としても正確。
  同じ理由で `kv-2-1` もクリップボードをやめて検食にしている
- マスク＋ヘアネット＋青いニトリル手袋で衛生表現を統一。顔が隠れるので肖像権の面でも扱いやすい

### （旧）FVのコントラストの調整

**2026-09-30 以降は文字を写真に乗せていないので、この調整（暗幕）はもう無い。** 以下は旧FVの記録。

写真の上に白文字を置いているので、キャプチャの画素から明るさを測った。
当初は本文の明るい部分が 6.73:1、最も明るい箇所が 2.66:1 まで落ちていたため、
**暗幕に横方向のグラデを重ねて「文字がある左側だけ」を落とした**（全面を濃くすると右のパネルの写真が死ぬ）。

| | 修正前 | 修正後 |
|---|---|---|
| 見出し（66px・基準3:1）明るい方10% | 4.17 | **9.88** |
| 見出し 最も明るい1% | 2.78 | **5.44** |
| 本文（16px・基準4.5:1）明るい方10% | 6.73 | **12.48** |

本文の最明1%が 2.72 のまま残るが、これは**白文字のアンチエイリアス縁**を背景として
拾っているだけ（12万画素の1%＝1200画素で、文字の縁がそれを超える）。
中央値 14.68・明るい方10% 12.48 なので実質は基準を満たしている。

### 提案：「食事形態」5段階を図解セクションにする

現行サイトのメディアに **`102`（2560×2413）「食事形態」の説明図**があった。
印刷物の撮影なので画像素材としては使えないが、書かれている内容が強い。

> 常食 ／ 一口大 ／ 粗刻み ／ 極刻み ／ ミキサー
> ※施設によってはムース食にも対応しております。

**これは給食委託の選定で決定的な情報**（施設側は「どの形態まで対応できるか」で選ぶ）。
いま強み②のカード1枚に押し込んでいるが、**5段階を並べた独立セクションに格上げする価値がある**。
実写も揃っている（ムース食＝`086`/`101`、刻み食＝`102`の元になった実物）ので、
撮り直せば図解として作れる。次フェーズで提案したい。

### OGP の注意

**LINE のトーク内カードは og:image を中央で正方形にクロップする。**
1200×630 の全面にコピーを敷くと両端が切り落とされるので、中央 630×630 に
社名・キャッチ・電話を収める。書き出したら実際にクロップして目で確認する：

```bash
ffmpeg -v error -i images/ogp.png -vf "crop=in_h:in_h:(in_w-in_h)/2:0,scale=600:600" -y /tmp/ogp-square.png
```

---

## 画像生成プロンプト

### 実写と生成の切り分け

| 用途 | 何を使うか | 理由 |
|---|---|---|
| **ギャラリー・強み②③・事業内容・About副** | **実写** | 本物である証明。毎月違う献立を出せているという事実が効く |
| **FV（縦ストライプ4パネル）** ※2026-09-30 から FV は写真なし。`kv-*` はどこでも使っていない | **生成** | 実写の行事食（くまのクッキー・トナカイのデザート）は**記録写真**で、真上から蛍光灯下で撮っているため奥行きも空気感もない。施設運営者に「うちの食事がこう変わる」を見せる絵にならない |
| **厨房・スタッフ・背景** | **生成 → 最終的に撮影** | 実写が1枚も存在しない |

FVに実写を当てて一度試したが、デザート中心で「幼稚園のおやつ」に寄って見えたため差し戻した。
**実写はギャラリーに置くほうが強い。**

### FV：縦ストライプ4パネル（`kv-*.svg` → 生成画像に差し替え）

**2026-10-02 に `kv-*` は12枚とも消した**（FV は 09-30 から写真なし）。プロンプトは作り直すときのために残す。

サイズは **640×1140（約9:16の縦長）**。4パネル × 3シーンで12枚。**2026-09-30 から FV で使うのはシーン①の4枚だけ**（残り8枚は下の節の候補）。

シーンごとに物語を持たせている。**パネル1→4で「作る → 盛る → 届ける → 喜ばれる」**の順。

| シーン | テーマ | パネル1 | パネル2 | パネル3 | パネル4 |
|---|---|---|---|---|---|
| ① | 毎月の行事食 | 厨房での調理（湯気・寄り） | 盛り付けの手元 | 完成した定食トレー | 食堂で食事する高齢者 |
| ② | 毎日の献立 | 管理栄養士が献立表を確認 | 大鍋で煮込む | 配膳カート（施設の廊下） | 職員が食事を運ぶ |
| ③ | 介護食・ムース食 | 刻み食・ソフト食の器 | ムース食（型で成形） | スタッフが見守る | 季節の行事食の盛り付け |

**まず シーン① の4枚だけ生成して動作確認 → 良ければ残り8枚**、という順で進められる。
（旧FVの記述。2026-09-30 から FV は縦組みの文字だけで写真を置いていない。以下の生成画像は FV では使っていない）

#### 共通の指定（全パネルの末尾に付ける）

```
Vertical composition, 9:16 portrait crop, subject placed in the upper two-thirds
(the lower third is covered by a dark gradient and text in the layout).
Style: natural window daylight, soft shadows, shallow depth of field,
muted warm palette with soft reds and creams, shot on 50mm f/2, editorial
documentary photography of Japanese healthcare food service. Calm, clean, trustworthy.

MUST NOT include: any text, letters, numbers, kanji, hiragana, katakana, signage,
labels, menus with readable characters, posters, watermarks, logos, brand marks,
calendars, or clocks with visible numerals.
No distorted or extra fingers. No exaggerated smiles. Not glossy advertising style.
Not overhead flat-lay under fluorescent light.
```

最後の2行が重要。**日本語を描かせると必ず崩れる**（前のモックの「採用惰報」「2226-83」がこれ）ので、
文字は一切入れさせず、すべてHTML側で組む。
`Not overhead flat-lay under fluorescent light` は、実写と同じ失敗を生成側で繰り返さないための指定。

#### シーン①：毎月の行事食

```
kv-1-1 ── Close-up of a Japanese cook in white uniform, hairnet and mask, ladling
soup from a large stainless pot in a commercial institutional kitchen. Steam rising,
backlit by a window. Hands and pot in focus, kitchen softly blurred behind.

kv-1-2 ── Close-up of gloved hands arranging a seasonal Japanese meal onto a
compartmented tray at a stainless counter. Colorful vegetables, careful plating.
Overhead-diagonal angle, not flat.

kv-1-3 ── A completed Japanese institutional meal tray photographed at a low
three-quarter angle on a wooden dining table: rice, miso soup, grilled fish,
simmered vegetables. Warm daylight from the side, soft background bokeh.

kv-1-4 ── An elderly Japanese woman seated at a bright dining room table in a care
facility, hands together before her meal, gentle natural expression. Soft window
light, other tables blurred behind.
```

#### シーン②：毎日の献立

```
kv-2-1 ── A Japanese registered dietitian in a white coat standing at a stainless
counter, reviewing a blank clipboard, calm and professional. Institutional kitchen
softly blurred behind. Clipboard paper is plain and blank.

kv-2-2 ── Wide-ish view of two cooks in white uniforms and hairnets working at a
large commercial gas range, big pots, steam, natural light from high windows.

kv-2-3 ── A stainless meal-delivery cart with covered trays standing in a clean,
bright corridor of a Japanese care facility. Soft daylight, handrail on the wall,
no people.

kv-2-4 ── A care worker in a pale pink uniform carrying a meal tray with both hands
through a bright dining room, mid-stride, seen from a slightly low angle.
```

#### シーン③：介護食・ムース食

```
kv-3-1 ── Close-up of finely chopped and softened Japanese dishes served in small
white bowls on a tray, texture clearly visible. Soft daylight, shallow depth of
field, clinical but appetizing.

kv-3-2 ── Close-up of pureed Japanese food shaped with molds into neat forms
(fish shape, vegetable shape) on a white plate, garnished simply. Delicate,
appetizing, soft daylight from the side.

kv-3-3 ── A care worker in a pale uniform seated beside an elderly person at a
dining table, attentively watching them eat, warm and unhurried. Soft window light,
shot from the side at eye level.

kv-3-4 ── Close-up of a seasonal Japanese celebration meal being plated: bright
garnishes, a small decorative element, careful arrangement on a patterned plate.
Diagonal angle, warm daylight.
```

### 残りの厨房・スタッフ枠

| ファイル | サイズ | プロンプト |
|---|---|---|
| `about-main` | 640×480 | `Two Japanese cooks in white uniforms and hairnets preparing vegetables at a stainless counter in a commercial institutional kitchen. Natural window light, steam, mid-shot.` |
| `strength-1` | 480×300 | `A Japanese registered dietitian in a white coat at a desk with a blank notebook and a calculator, planning, calm and professional. Soft daylight.` |
| `strength-4` | 480×300 | `A cook in white uniform and a care facility staff member in a pale uniform talking beside a serving counter, relaxed and cooperative. Soft daylight.` |
| `recruit` | 640×480 | `A cheerful Japanese kitchen staff member in white uniform, hairnet and apron, standing in a bright commercial kitchen, arms relaxed, warm natural expression.` |
| `stats-bg` | 1600×500 | `Wide empty view of a clean commercial institutional kitchen, stainless counters and equipment, soft daylight, no people. Calm and orderly.` |
| `contact-bg` | 1600×600 | `Wide view of a bright dining room in a Japanese care facility, empty tables set for a meal, soft window light, no people.` |

いずれも上記「共通の指定」を末尾に付ける。`/image2` にそのまま渡せる。

### 生成は暫定。最終的には撮影

給食委託は**「この厨房で、このスタッフが作っています」が言えるかどうか**で決まる業種。
生成画像は提案とレイアウト確認までの繋ぎで、公開前に撮影1日（厨房・スタッフ・提供シーン）に
差し替えるのが前提。ギャラリーは実写があるのでそのまま使える。

### OGP の注意

**LINE のトーク内カードは og:image を中央で正方形にクロップする。**
1200×630 の全面にコピーを敷くと両端が切り落とされるので、中央 630×630 に
社名・キャッチ・電話を収める。書き出したら実際にクロップして目で確認する：

```bash
ffmpeg -v error -i images/ogp.png -vf "crop=in_h:in_h:(in_w-in_h)/2:0,scale=600:600" -y /tmp/ogp-square.png
```

---

## 検証済みの項目

ヘッドレス Chrome（CDP）の実測で確認したもの。

- コンテンツ幅 **1140px ちょうど**／PC 余白 32px（2rem）／768px以下 16px（1rem）
- 横スクロールなし（375px / 390px / 741px / 1440px）
- コンソールエラー 0／**壊れ画像 0**（33枚すべて読み込み）
- `h1` は1つ。見出し階層は h1 → h2 で連続
- **タップ領域 44px 未満 0件**（WCAG 2.2 SC 2.5.8 の 24px は全件クリア）
- **ドロワーを開いた状態で×が押せる**（44×44・最前面・`aria-expanded` とラベルが連動）
- **紙芝居のセンタリング**：札の左右の余白が 202px / 202px で完全中央
- **紙芝居の尺**：4札にほぼ均等配分（進捗0→1で 01→02→03→04）
- FV（2026-09-30・縦組み）：320〜1440px の8幅で、受託の一文1列・見出し2列・ボタン2つが最初の画面に入る（320px はボタン2段・下端 568px）／横スクロールなし／縦組みの塊は左右中央（左右の余白が同じ）。動きが終わると切り抜きが外れる（ボタンの影・フォーカス枠が切れない）。動きを減らす設定では最初から見える。SP固定バーはFVを過ぎると出る
- カウントアップが 8 / 1 / 50 で着地
- reveal 18/18 発火。リロードで途中位置が復元されても取り残しなし
- `tel:` リンク 5本
- フルページ実測：PC 1440×9137 ／ SP 390×9814（画像33点・壊れ0）
- **FVの白文字コントラスト**：見出し 明るい方10%で 9.88:1、本文 12.48:1（実測値は上記）

### キャプチャ時の注意（`dvh` を使っているため）

フルページを撮るときに**ビューポートを縦長にしてはいけない**。
FVの `min(100dvh, 46rem)` と紙芝居の `78dvh * 4` が膨張して、
1440×9263 のページが 1440×35355 になる。
ビューポートは実機サイズで固定し、`Page.captureScreenshot` の
`captureBeyondViewport: true` だけでページ全体を撮る。

## 使っていない CSS・画像の削除（2026-10-02）

新庄さんの決定（10-02「使っていない CSS と画像を消す」）。候補は「優しいカラー選定」の会話の点検結果。消す前に、HTML・PHP・JS・`content/`・`cf7/`・DESIGN.md・本番と同じ中身の記録（Git の `0011fd2`）・Local の WP のデータベース（本文・メタ・オプション）を探し、どこにも出てこないことを確かめた。戻すときは Git から取り出す。

**CSS（18クラス）**

| 消した物 | あった所 |
|---|---|
| `c-card` 一式（`c-card`・`--link`・`__media`・`__img`・`__body`・`__title`・`__text`・`__foot`・`__head`・`__icon`・`__icon-svg` の11クラス） | `sass/object/component/_card.scss`（ファイルごと）と `style.scss` の `@use` |
| `c-btn__icon` | `_btn.scss` |
| `c-media--3x4` | `_media.scss` |
| `l-section--sub` | `layout/_section.scss` |
| `p-area-map__todo` | `_area-map.scss`（画面の【要確認】注記は 09-30 に外していた） |
| `p-form__select` | `_form.scss`（入力欄・フォーカス・誤りの4か所のセレクタから外した。フォームに select は無い） |
| `u-nav-only`・`u-sp-only` | `_utility.scss` |

出力 CSS（LP・圧縮）は 90,088 → 88,506 バイト。WP（展開）も同じ Sass から出し直した。Contact Form 7 が自分で付けるクラス（`wpcf7-*`）は対象外。

**画像（LP 13枚・1,035KB／WP テーマ 14枚・1,134KB）**

- LP の `images/`：`kv-1-1〜4`・`kv-2-1〜4`・`kv-3-1〜4`（12枚）・`contact-bg.jpg`
- WP テーマの `assets/images/`：上と同じ13枚と `ogp-bg.jpg`（WP には OGP の版下が無い。LP の `ogp.html` が使う `images/ogp-bg.jpg` は残す）
- `images-src/optimize.py` の規則から `contact-bg` を外した（流し直すと `images/` に戻るため）。元画像 `images-src/contact-bg.jpg` は残した

**消していない物**

- WP テーマの `g-*.jpg`（16枚）・`gallery-*.jpg`（8枚）。Local の WP では、食事ギャラリーとお知らせのアイキャッチ（37件）はどれもメディア（`uploads/`）の写しから出ていて、テーマのファイルを直接指す本文・メタ・オプションは0件。テーマの `g-01・02・03・06・09・10` は `seed.php` がアイキャッチを取り込むときの元。`g-11〜16`・`gallery-1〜8` はどのコードからも呼ばれていない。消すかは別に決める
- P3 で足した `c-skip-link`・favicon・`inc/schema.php` など、使っている部品

## 未実装・次にやること

- [ ] **最終的に厨房・スタッフは撮影1日で実写に差し替え**
- [ ] `images/ogp.png` の作成（中央630×630セーフゾーン）
- [x] ロゴの実データ差し込み（2026-09-30。現行サイトの横組み画像から作った暫定の画質。ベクター原本が届いたら差し替え）
- [ ] 「食事形態5段階」の図解セクション新設（上記の提案）
- [ ] GA4 の設置（現行サイトは3年以上計測が止まっている）
- [ ] お問い合わせフォームの接続先確定。`contact.html` は見本のフォームで送信しない（`action="#"`）。
      WordPress 版は 2026-10-01 に Contact Form 7 で作った（Local で送信まで確認。`wp-theme/README.md`「お問い合わせのフォーム」）。
      残りは送信先メールアドレス・スパム対策（鍵の発行）・自動返信の有無で、どれも先方確認待ち
      ※ 2026-09-30 に `contact.html` がフォルダから消えていたのを、08-13 の本番デプロイから取り戻した
- [ ] WordPress 化（対応表は上記）。固定ページ5つ（お問い合わせを含む）の本文は `wp-theme/content/` から流し込み済み。お問い合わせは Contact Form 7（送信先とスパム対策が未決）
- [ ] JSON-LD（Organization / LocalBusiness）の追加。住所・電話・営業時間は揃っている
- [ ] 実写のWebP化（現状JPEG。ギャラリー8枚で約1MB）

### hallmark check の P3（2026-10-02、Hallmark の会話 → 矢印線の会話へ）

実装は矢印線の会話（体制どおり）。終わったら Hallmark の会話が `hallmark check` で検収する。行番号は 10-02 11:20 時点。
**静的版を変えたら WP（テンプレート・`export-content.py`・`assets/`）もそろえる。**

新庄さんが決めたこと（10-02）：提案用の文（採用の「（ご記入をお願いします）」4か所・申し送り2か所）は**残す**／メタ説明文は**画面の文から書き直してよい**（新しい事実は足さない）／favicon は**ロゴから作る**／節の英字ラベルは**減らさない**。
下層見出しの 4.5:1 と英字ラベルの `aria-hidden` は、矢印線が 4スキルレビュー P1 で直し済みなので外した。

- [x] **S-23 は案件の決まりで外す**：DESIGN.md §3 に `<!-- hallmark-ignore-project S-23 見出しの改行は br で決める（§3）。住所・要項の改行も内容の改行 -->` を1行。監視 hook はこの1行でこの案件の S-23 を見なくなる → **済**（DESIGN.md §3 の頭に1行）
- [x] **S-11 色の直値2件** `sass/layout/_under.scss`：暗幕のグラデーション `rgb(20 16 15 / .82 .62 .50)`（51〜53行）とリード文 `rgb(255 255 255 / 0.88)`（98行）をトークンへ（DESIGN.md に足してから使う。パンくずの `--_text`・`--_sep` の白も同じ書き方に） → **済**（`--color-scrim`・`--color-on-photo-sub` を _variables と DESIGN.md §2 に足して使う。パンくずの白も `color-mix`。値は前と同じ）
- [x] **下層見出しの英字ラベルを白に**（10-02 新庄さん「英字ラベルも白にそろえて」）：`sass/layout/_under.scss:82` `.l-pagehead__en` の `var(--color-accent-pale)` → `var(--color-white)`。写真の上の大きな見出しだけ（125行の記事ページの薄い見出しの朱は明るい地なのでそのまま）。白は pale より明るいので、4スキルレビューで測った 4.54:1 より上がる。DESIGN.md 466行（「英字ラベルを `--color-accent-pale`」）も白に直す。WP は `assets/css` を出し直すだけ → **済**（`var(--color-white)`。DESIGN.md §7 にも記録。記事ページの薄い見出しの朱はそのまま）
- [x] **S-19 光学調整の margin 2件 → translate**（checks-auto §3）：`_form.scss:178` `.p-form__check` の `margin-block-start: 0.2rem` → `translate: 0 0.2rem`／`_reading.scss:179` `.p-reading__mark` の線の `margin-block-start: -1px` → `translate: 0 -1px`（`::after` の rotate と組み合わさっても位置は同じ） → **済**（2件とも `translate`。画素で比べて1px未満の丸めだけ）
- [x] **S-19 理由はあるが例外の形になっていない12件**：直前の行を `// hallmark-ignore S-19 <いまのコメントの理由>` にする。`_card:57`（下端に寄せる auto）`_viewer:29`（dialog の margin:0）`_entry:16` `_facility:26` `_stats:75` `_stats:168`（中央寄せ auto）`_texture:118`（margin:0）`_arcgal:26` `_company:191`（数字と単位の間 2px）`_company:129`（ラベルとタグの間）`_stats:98`（下の目盛りのぶん）`_strength:153`（図と名前の間） → **12件済**（`_arcgal:26` も、手放しの連絡のあとに入れた）
- [x] **S-29 トップの締め**「担当者が迅速に対応いたします。」を消して、ほかの10ページの締めの文にそろえる（PC で2行になる S-04 も解消）。確認シートの追補17（返事の早さ）の先方回答とつながるので、扱いは矢印線の判断で。WP の締め（`template-parts/cta.php` 等）も → **やらない（残す）**：確認のお願いの追補17（返事の早さ）と、確定した送付資料が「トップに書いています」と言っているため。先方の答えが来てから消すか決める
- [x] **メタ説明文**：トップ（`index.html:7` の「ソフト食」→ 画面の5段階の言葉に）、お知らせ `news-882`・`news-884`（時候の挨拶 → 記事の1文目から）。og:description も → **済**（トップは画面の5段階の言葉、news-882・884 は記事の要点の1文。og:description も。`content/meta.json` も書き出し直し）
- [x] **og:type**：固定ページと一覧（about・service・recruit・gallery・news・contact・privacy）は `website`。記事（news-8xx・998）だけ `article` → **済**（about・service・recruit・news・contact・privacy は website）。（`gallery.html` も済。10-02 午後、ギャラリーの会話から手放しの連絡のあと）
- [x] **favicon**：`images/logo.svg` のマークから favicon（svg＋ico）と apple-touch-icon（180px）。全ページと WP の head に → **済（形を変えた）**：`logo.svg` は仮画像だったので、`logo.png` の左のマークから `favicon.ico`（16/32/48）と `images/apple-touch-icon.png`（180・白地）を作った。svg は元データが無いので作らない（ロゴの元データが届いたら作り直す＝確認のお願い7）。10ページと WP の header.php。（`gallery.html` も済。10-02 午後、ギャラリーの会話から手放しの連絡のあと）
- [x] **JSON-LD**：トップ（と会社概要）に Organization／LocalBusiness（住所・電話・営業時間は画面と同じ値、url は canonical）、下層に BreadcrumbList、記事3本に NewsArticle（見出し・日付は画面と同じ）。WP は wp_head から → **済**：トップと会社概要に Organization（社名・所在地・電話・設立日。url は canonical）、下層に BreadcrumbList、記事3本に NewsArticle。WP は `inc/schema.php`（wp_head）でパンくずと同じ項目を使う（`jinfoods_breadcrumb_items()`）。**営業時間は入れていない**（確認のお願い17の答え待ち。検索エンジン向けに未確認の時間を出さない）。（`gallery.html` も済。10-02 午後、ギャラリーの会話から手放しの連絡のあと）
- [x] **スキップリンク**「本文へスキップ」を body の先頭に（main.js:49 が `.js-skip` を待っている）。main に id → **済**：Tab で選んだときだけ左上に出る部品 `c-skip-link`（新規 `_skip-link.scss`、`--z-skip`）。前の WP は `u-vh` だけで選んでも見えなかったので WP も同じ部品に。main に `id="main"`。（`gallery.html` も済。10-02 午後、ギャラリーの会話から手放しの連絡のあと）
- [x] **ランドマーク**：ドロワーの `div > ul` を `nav aria-label`、SP 固定バー `.p-spbar` を `nav aria-label`（どちらも WP の header／footer も） → **済**（ドロワーは ul を `<nav aria-label="メインメニュー">` で包む、SP 固定バーは `<nav aria-label="連絡先">`。WP の header／footer も）。（`gallery.html` も済。10-02 午後、ギャラリーの会話から手放しの連絡のあと）
- [x] **見出しの代わりの p**：フッターの「事業について」「会社情報」、帯の題（`service.html:124・299`・`gallery.html:171`）、電話の案内の題（`contact.html:109`）を h2／h3 に（UA の見出しの大きさが乗らないか確かめる） → **済**（フッター2つと電話の案内は h2、事業内容の #field の帯は h3・ページ下の帯は h2。どのクラスも大きさと太さを自分で持っていて、10ページの高さは1pxも変わらず）。`gallery.html:171` の帯は、ギャラリーの会話の作業で格子の中の「締めのマス」（一覧のリンクの1つ）に変わったので見出しにはしない。gallery.html のフッター2つは h2 にした
- [x] **パンくず** `nav > span` を `ol > li` に（区切りの CSS も）。WP のパンくず関数も → **済**（`.c-breadcrumb__list` に横並びを移した。WP の `jinfoods_breadcrumb()` も）。（`gallery.html` も済。10-02 午後、ギャラリーの会話から手放しの連絡のあと）
- [x] **名前のない section**：見出しのある section は見出しに id を付けて `aria-labelledby`、見出しの無い section（`gallery.html:105・168`・`service.html:296`・`news.html:105`・`privacy.html:105`）は div に → **済**：見出しのある section は見出しに id＋`aria-labelledby`、見出しの無いもの（news・privacy、WP の 404・一覧3つ・page.php の外側）は div。privacy の外側は本文全体を包んでいるので名前を付けず div。WP の締め（cta.php）は2通りの h2 に同じ id。`gallery.html`・`archive-gallery.php` の一覧の区画も div（見出しが無いため）
- [x] **リンクの名前**：トップ「一覧を見る」（`index.html:544`）に `<span class="u-vh">お知らせの</span>`／記事の「前のお知らせ」「次のお知らせ」に行き先の題を `u-vh` で／Google マップ（`about.html:334`）に `<span class="u-vh">（新しいタブで開きます）</span>`／お問い合わせの同意の「プライバシーポリシー」は新しいタブで開き、同じく予告（静的版と `wp-theme/cf7/contact-form.html`） → **済**（4か所とも。WP の single.php は行き先の題を `get_the_title()` で出す。CF7 のフォームは seed-cf7.php で Local にも流した）
- [x] **受託施設の詳細**：詳細の入れ物に `aria-live="polite"`（押すと出た内容が読まれる）。案内「施設名を押すと…」（`index.html:361`）は JS が効いているときだけ出す（JS が無いと詳細は出ないため） → **済（形を変えた）**：入れ物に aria-live を付けると何も開いていないときに余白が出るので、拡大表示と同じ読み上げ専用の欄（`u-vh js-facility-status`）に「はんだの郷 所在地：大分市判田。 種別：高齢者施設。」を書く。閉じたら空にし、開き直しても読まれる。案内は `hidden` にして JS が出す（JS なしでは出ないことを確認）
- [x] **要素の選び直し**：強み01 の `<article class="p-strength__hero">`（`index.html:383`）を div に（単独で配る内容ではない。02〜04 は li） → **済**（静的版と WP の front-page.php とも div）
- [x] 変えないと決めたもの（理由だけ残す）：FV の写真9枚の `loading="lazy"`（3D が描くので写真は代わり。画面内の lazy はすぐ読まれる）／`recruit.jpg` の loading（PC で最初の画面に入るかを測ってから決める）／FV と事業内容の節で同じ「事業内容を見る」の行き先が違う件（文脈で 2.4.4 は満たす） → **変えない**（理由はこの行のとおり）
