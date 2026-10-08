from hashlib import sha256
from pathlib import Path
import sys
from zipfile import ZipFile


archive_path = Path(sys.argv[1]).resolve()
required = {
    "taabeer-deployment-manager/taabeer-deployment-manager.php",
    "taabeer-deployment-manager/inc/class-taabeer-deployment-manager.php",
    "taabeer-deployment-manager/uninstall.php",
    "taabeer-deployment-manager/README.txt",
}
with ZipFile(archive_path) as archive:
    bad = archive.testzip()
    names = archive.namelist()
    missing = sorted(required.difference(names))
    roots = sorted({name.split("/", 1)[0] for name in names})
if bad or missing or roots != ["taabeer-deployment-manager"]:
    raise SystemExit(f"Invalid plugin: bad={bad!r}, missing={missing!r}, roots={roots!r}")
print(f"files={len(names)}")
print(f"size_bytes={archive_path.stat().st_size}")
print(f"sha256={sha256(archive_path.read_bytes()).hexdigest()}")
print("plugin_package_ok=true")

