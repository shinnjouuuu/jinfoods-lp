# jin-foods-website4

株式会社JIN foods のコーポレートサイト（別デザイン案）。内容は jin-foods-site1 と同じで、UI とアニメーションを上品・ミニマルな方向に作り替えたもの。

## プレビュー

```sh
python3 -m http.server 8004 --bind 127.0.0.1
```

http://127.0.0.1:8004 を開く。静的 HTML / CSS / JS。

## Renderへのデプロイ

`render.yaml` は Render の Static Site 用設定です。ビルドコマンドは
`python3 tools/build_static.py`、公開ディレクトリは `dist`、ブランチは `main` です。
ビルドでは現在の HTML・CSS・JavaScript・画像を `dist` にコピーします。

APIから操作する場合は、Git管理対象外の `.env.local` に `RENDER_API_KEY` を設定します。
このキーはデプロイ操作用で、サイトの公開ファイルやRenderの実行環境には含めません。

## 構成

- `index.html` トップページ
- `css/base.css` 色・書体などの共通設定
- `css/components.css` ヘッダー・メニュー・ボタン・フッター・オープニング
- `css/sections.css` トップの各セクション
- `css/motion.css` オープニングとヒーローの動き
- `css/pages.css` 下層ページ
- `js/main.js` と `js/modules/` 動きごとのスクリプト
- `assets/` 写真とロゴ（site1 から必要な分だけコピー）

## 下層ページ

`service.html` `meals.html` `company.html` `recruit.html` `news.html` `contact.html` `privacy.html` `sitemap.html` は生成物。直接は編集せず、元を直してから作り直す。

```sh
python3 tools/build_pages.py
```

- ヘッダー・フッター・最後のお問い合わせ欄は `index.html` から写すので、トップのそれを直したら上のコマンドを再実行する。
- 各ページの本文は `tools/pages/<名前>.html`。先頭のコメントにタイトル・英字ラベル・リード文・写真を書く。
- 毎日の食事（49品）は `tools/data/meals.json`、お知らせは `tools/data/news.json` から作る。
- お問い合わせフォームは確認画面のあと FormSubmit の AJAX エンドポイントへ送信する（`js/modules/contact-form.js`）。最初の送信後、`info@jin-oita.com` に届く確認メールのリンクを開いてフォームを有効化する必要がある。有効化前はメール配信が保留されるため、公開前に実際の受信確認を行う。外部サービスへ入力内容を送るため、送信先アドレスやサービスを変更する場合はフォームの案内とプライバシーポリシーも更新する。
