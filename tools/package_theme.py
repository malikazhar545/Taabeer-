from pathlib import Path
import re
from zipfile import ZIP_DEFLATED, ZipFile


ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "wordpress" / "wp-content" / "themes" / "taabeer"
OUTPUT_DIR = ROOT / "artifacts"
style = (THEME / "style.css").read_text(encoding="utf-8")
match = re.search(r"^Version:\s*([^\s]+)", style, flags=re.MULTILINE)
if not match:
    raise SystemExit("Theme version is missing from style.css")
VERSION = match.group(1)
OUTPUT = OUTPUT_DIR / f"taabeer-theme-{VERSION}.zip"

OUTPUT_DIR.mkdir(exist_ok=True)

with ZipFile(OUTPUT, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
    for path in sorted(THEME.rglob("*")):
        if path.is_file():
            archive.write(path, Path("taabeer") / path.relative_to(THEME))

print(OUTPUT)
