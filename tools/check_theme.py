from pathlib import Path

ROOT = Path(__file__).resolve().parents[1] / "wordpress" / "wp-content" / "themes" / "taabeer"
TEXT_SUFFIXES = {".php", ".css", ".js", ".json", ".txt", ".md"}
SUSPICIOUS = ("\u00c3", "\u00c2", "\u00e2\u20ac\u201d", "\u00e2\u20ac\u201c", "\u00d8", "\u00db")

issues = []
for path in ROOT.rglob("*"):
    if not path.is_file() or path.suffix.lower() not in TEXT_SUFFIXES:
        continue
    text = path.read_text(encoding="utf-8")
    hits = [needle for needle in SUSPICIOUS if needle in text]
    if hits:
        issues.append((str(path.relative_to(ROOT)), hits))

if issues:
    for file_name, hits in issues:
        print(f"{file_name}: suspicious sequences {hits!r}")
    raise SystemExit(1)

print("Theme text files are valid UTF-8 with no common mojibake sequences.")
