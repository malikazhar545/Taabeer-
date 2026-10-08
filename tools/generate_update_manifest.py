import argparse
import hashlib
import json
from datetime import datetime, timezone
from pathlib import Path
from zipfile import ZipFile


def theme_version(archive: Path) -> str:
    with ZipFile(archive) as package:
        style = package.read("taabeer/style.css").decode("utf-8")
    for line in style.splitlines():
        if line.startswith("Version:"):
            return line.split(":", 1)[1].strip()
    raise SystemExit("Theme package has no Version header")


parser = argparse.ArgumentParser()
parser.add_argument("package", type=Path)
parser.add_argument("--commit", default="")
parser.add_argument("--notes", default="TAABEER theme code update.")
parser.add_argument("--output", type=Path, default=Path("artifacts/taabeer-update.json"))
args = parser.parse_args()

package = args.package.resolve()
manifest = {
    "version": theme_version(package),
    "theme": "taabeer",
    "package_asset": package.name,
    "sha256": hashlib.sha256(package.read_bytes()).hexdigest(),
    "commit": args.commit,
    "built_at": datetime.now(timezone.utc).isoformat(),
    "notes": args.notes,
}
args.output.parent.mkdir(parents=True, exist_ok=True)
args.output.write_text(json.dumps(manifest, indent=2) + "\n", encoding="utf-8")
print(args.output)

