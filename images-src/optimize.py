"""
images-src/ の元画像から、配信用の images/ を作る（同じファイル名の JPEG のまま、縮めて圧縮し直す）。
WordPress テーマの wp-theme/jinfoods/assets/images/ に同じ名前があれば、そちらも同じものに置き換える。

  cd ~/projects/lp/jinfoods-lp && python3 images-src/optimize.py

- 幅の上限は「その画像が表示される最大の幅（CSS px）× 2」（高解像度の画面でもぼやけない大きさ）。
  表示の大きさを変えたら、下の表の幅も直してから流し直す
- 縦横比は変えない。上限より小さい画像は縮めない（引き伸ばさない）
- 元画像はここ（images-src/）に残す。images/ を直接編集しない
- 2026-10-01 作成（Lighthouse の「適切なサイズの画像」「次世代フォーマット」の指摘から）
"""
import io
import os
import re
import sys

from PIL import Image

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, 'images-src')
OUTS = [os.path.join(ROOT, 'images'), os.path.join(ROOT, 'wp-theme', 'jinfoods', 'assets', 'images')]

# (ファイル名の正規表現, 幅の上限 px, JPEG の品質, どこで・最大何 px で表示しているか)
RULES = [
    # g-* は食事ギャラリーのページの拡大表示（最大 640px）と、お知らせの記事の写真（news-998）。元画像（800px）のまま。
    # 格子の小さい写真は下の THUMBS で g-XX-s を別に書き出す（2026-10-01、C案）
    (r'^g-\d+\.jpg$', 800, 80, '食事ギャラリーの拡大表示・お知らせの記事。最大 640px'),
    (r'^(gallery-\d+|menu-\d+)\.jpg$', 600, 80, '食事の写真のタイル（トップ・事業内容の献立）。最大 276px'),
    (r'^service-[12]\.jpg$', 1120, 80, 'トップの事業内容・事業内容ページ。最大 556px'),
    (r'^(about-sub|about-main)\.jpg$', 640, 80, 'トップ ABOUT・会社概要。最大 296px'),
    (r'^strength-[134]\.jpg$', 480, 80, '強みの小さい写真（88px）・会社概要の体制（最大 176px）'),
    (r'^(strength-2|recruit|mousse-real)\.jpg$', 960, 80, '強みの主役・採用・事業内容。最大 509px'),
    (r'^texture-hero\.jpg$', 1280, 80, '事業内容の5段階の写真。最大 736px'),
    (r'^head-.*\.jpg$', 1440, 78, '下層のページ見出しの背景（全幅・上に暗幕）'),
    # contact-bg は 2026-10-02 にどこでも使っていないので images/ から消した（元画像はここに残す）。書き出すと images/ に戻るので規則から外した
    (r'^stats-bg\.jpg$', 1280, 62, '暗幕の下に 14〜22% の濃さで敷くだけ'),
]


def encode(path, width, quality):
    im = Image.open(path)
    im = im.convert('RGB')
    w, h = im.size
    if w > width:
        im = im.resize((width, round(h * width / w)), Image.LANCZOS)
    buf = io.BytesIO()
    im.save(buf, 'JPEG', quality=quality, optimize=True, progressive=True)
    return buf.getvalue(), im.size


# WebP も書き出す画像（<picture> の <source type="image/webp"> で出す。<img> は JPEG のまま＝WebP を表示できない古い端末向け）。
# いまは LP の食事の写真のタイルだけ（枚数が多く、食事ギャラリーのページの重さのほとんどを占めるため）
WEBP = [(r'^g-\d+\.jpg$', 800, 76), (r'^(gallery-\d+|menu-\d+)\.jpg$', 600, 76)]
# 小さい版（名前に -s を付けて LP の images/ にだけ書き出す。JPEG と WebP）。
# (ファイル名の正規表現, 幅 px, JPEG の品質, WebP の品質, どこで・最大何 px で表示しているか)
THUMBS = [(r'^g-\d+\.jpg$', 400, 78, 74, '食事ギャラリーのページの格子（3〜6列）。最大 187px')]
# WP テーマにも WebP を置く画像。固定ページ「事業内容」の本文（export-content.py で LP から書き出す）が
# <picture> でテーマの WebP を直接指すため（無いと 404 になり、<img> の JPEG にも戻らず画像が出ない）。
# g-* / gallery-* は WP では投稿のアイキャッチ（seed.php がメディアに取り込む）から出すので足さない
WEBP_THEME = r'^menu-\d+\.jpg$'


def encode_webp(path, width, quality):
    im = Image.open(path).convert('RGB')
    w, h = im.size
    if w > width:
        im = im.resize((width, round(h * width / w)), Image.LANCZOS)
    buf = io.BytesIO()
    im.save(buf, 'WEBP', quality=quality, method=6)
    return buf.getvalue()


def main():
    total_before = total_after = 0
    for name in sorted(os.listdir(SRC)):
        rule = next((r for r in RULES if re.match(r[0], name)), None)
        if not rule:
            continue
        data, size = encode(os.path.join(SRC, name), rule[1], rule[2])
        before = os.path.getsize(os.path.join(SRC, name))
        # 元より大きくなるなら元のまま使う（すでに小さく圧縮されている画像）
        if len(data) >= before:
            data = open(os.path.join(SRC, name), 'rb').read()
        for out in OUTS:
            dst = os.path.join(out, name)
            if out != OUTS[0] and not os.path.exists(dst):
                continue  # WP テーマに無い画像（gallery.html だけで使う g-17〜36 など）は足さない
            if os.path.exists(dst) and open(dst, 'rb').read() == data:
                continue
            with open(dst, 'wb') as f:
                f.write(data)
        total_before += before
        total_after += len(data)
        print('%-18s %4dx%-4d %4dKB → %4dKB' % (name, size[0], size[1], before // 1024, len(data) // 1024))
    print('合計 %dKB → %dKB（%d%%）' % (total_before // 1024, total_after // 1024, round(100 * total_after / max(total_before, 1))))
    webp_total = 0
    for name in sorted(os.listdir(SRC)):
        rule = next((r for r in WEBP if re.match(r[0], name)), None)
        if not rule:
            continue
        data = encode_webp(os.path.join(SRC, name), rule[1], rule[2])
        for out in (OUTS if re.match(WEBP_THEME, name) else OUTS[:1]):
            dst = os.path.join(out, name[:-4] + '.webp')
            if not (os.path.exists(dst) and open(dst, 'rb').read() == data):
                with open(dst, 'wb') as f:
                    f.write(data)
        webp_total += len(data)
    print('WebP（LP の images/。menu-* は WP テーマにも）: %dKB' % (webp_total // 1024))
    thumb_total = 0
    for name in sorted(os.listdir(SRC)):
        rule = next((r for r in THUMBS if re.match(r[0], name)), None)
        if not rule:
            continue
        jpg, _ = encode(os.path.join(SRC, name), rule[1], rule[2])
        webp = encode_webp(os.path.join(SRC, name), rule[1], rule[3])
        for data, ext in ((jpg, '.jpg'), (webp, '.webp')):
            dst = os.path.join(OUTS[0], name[:-4] + '-s' + ext)
            if not (os.path.exists(dst) and open(dst, 'rb').read() == data):
                with open(dst, 'wb') as f:
                    f.write(data)
            thumb_total += len(data)
    print('小さい版（-s、LP の images/ だけ）: %dKB' % (thumb_total // 1024))


if __name__ == '__main__':
    sys.exit(main())
