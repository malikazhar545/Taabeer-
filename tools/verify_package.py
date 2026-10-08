from hashlib import sha256
from pathlib import Path
import re
import sys
from zipfile import ZipFile


ROOT = Path(__file__).resolve().parents[1]
if len(sys.argv) > 1:
    archive_path = Path(sys.argv[1]).resolve()
else:
    style = (ROOT / "wordpress" / "wp-content" / "themes" / "taabeer" / "style.css").read_text(encoding="utf-8")
    version = re.search(r"^Version:\s*([^\s]+)", style, flags=re.MULTILINE).group(1)
    archive_path = ROOT / "artifacts" / f"taabeer-theme-{version}.zip"

with ZipFile(archive_path) as archive:
    bad = archive.testzip()
    names = archive.namelist()
    required = {
        "taabeer/style.css",
        "taabeer/functions.php",
        "taabeer/theme.json",
        "taabeer/screenshot.png",
        "taabeer/inc/demo-import.php",
        "taabeer/archive-product.php",
        "taabeer/single-product.php",
    }
    missing = sorted(required.difference(names))
    roots = sorted({name.split("/", 1)[0] for name in names})

if bad or missing or roots != ["taabeer"]:
    raise SystemExit(f"Invalid package: bad={bad!r}, missing={missing!r}, roots={roots!r}")

digest = sha256(archive_path.read_bytes()).hexdigest()
print(f"files={len(names)}")
print(f"size_bytes={archive_path.stat().st_size}")
print(f"sha256={digest}")
print("package_ok=true")
