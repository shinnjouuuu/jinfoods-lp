"""Copy the checked-in website into Render's static publish directory."""
from pathlib import Path
import shutil


ROOT = Path(__file__).resolve().parent.parent
PUBLISH = ROOT / 'dist'


def main() -> None:
    if PUBLISH.exists():
        shutil.rmtree(PUBLISH)
    PUBLISH.mkdir()

    for folder in ('assets', 'css', 'js'):
        shutil.copytree(ROOT / folder, PUBLISH / folder,
                        ignore=shutil.ignore_patterns('.DS_Store', '__pycache__'))
    for page in ROOT.glob('*.html'):
        shutil.copy2(page, PUBLISH / page.name)
    for name in ('robots.txt', 'sitemap.xml'):
        source = ROOT / name
        if source.is_file():
            shutil.copy2(source, PUBLISH / name)

    print(f'Published {len(list(PUBLISH.glob("*.html")))} HTML pages to dist/')


if __name__ == '__main__':
    main()
