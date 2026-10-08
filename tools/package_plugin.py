from pathlib import Path
import re
from zipfile import ZIP_DEFLATED, ZipFile


ROOT = Path(__file__).resolve().parents[1]
PLUGIN = ROOT / "wordpress" / "wp-content" / "plugins" / "taabeer-deployment-manager"
OUTPUT_DIR = ROOT / "artifacts"
header = (PLUGIN / "taabeer-deployment-manager.php").read_text(encoding="utf-8")
version = re.search(r"^ \* Version:\s*([^\s]+)", header, flags=re.MULTILINE).group(1)
output = OUTPUT_DIR / f"taabeer-deployment-manager-{version}.zip"

OUTPUT_DIR.mkdir(exist_ok=True)
with ZipFile(output, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
    for path in sorted(PLUGIN.rglob("*")):
        if path.is_file():
            archive.write(path, Path("taabeer-deployment-manager") / path.relative_to(PLUGIN))
print(output)

