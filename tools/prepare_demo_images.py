from pathlib import Path
from PIL import Image


SOURCE = Path(r"C:\Users\ma944\AppData\Local\Temp\browser-use\assets\0c8e6c65-0b49-434c-a4a8-8e59582fcaae")
DESTINATION = Path(r"D:\Taabeer\wordpress\wp-content\themes\taabeer\assets\images\demo")

IMAGES = {
    "4354e58f3d0d8ea0": "hero-weaver.webp",
    "6e65ddbdaecf3ea4": "collection-wear.webp",
    "a55d80c032fe1391": "collection-adornment.webp",
    "de475c21a835bef8": "collection-living.webp",
    "49902a33f1224eef": "collection-expression.webp",
    "ea0ea430948e863d": "collection-leather.webp",
    "96274a9821dc1ab5": "about-material.webp",
    "cfef1fe24002cfba": "story-textile.webp",
    "89204f698cb829a5": "story-studio.webp",
    "d30b165f9ae633d5": "story-object.webp",
    "752d599a1d272cff": "discover-pakistan.webp",
    "06d944986dfc5229": "region-punjab.webp",
    "c759d89e61900955": "region-sindh.webp",
    "41fb02a56322aa66": "region-north.webp",
}


def main() -> None:
    DESTINATION.mkdir(parents=True, exist_ok=True)
    for source_name, output_name in IMAGES.items():
        source = SOURCE / source_name
        if not source.exists():
            raise FileNotFoundError(source)
        with Image.open(source) as image:
            image = image.convert("RGB")
            image.thumbnail((2200, 2200), Image.Resampling.LANCZOS)
            image.save(DESTINATION / output_name, "WEBP", quality=84, method=6)
            print(f"{output_name}: {image.width}x{image.height}")


if __name__ == "__main__":
    main()
