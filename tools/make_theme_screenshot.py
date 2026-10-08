from pathlib import Path

from PIL import Image, ImageDraw, ImageFont, ImageOps


ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "wordpress" / "wp-content" / "themes" / "taabeer"
SOURCE = THEME / "assets" / "images" / "demo" / "hero-weaver.webp"
OUTPUT = THEME / "screenshot.png"

WIDTH, HEIGHT = 1200, 900
GREEN = "#0D2C25"
IVORY = "#F7F3EA"


def font(name: str, size: int) -> ImageFont.FreeTypeFont:
    path = Path("C:/Windows/Fonts") / name
    return ImageFont.truetype(str(path), size=size)


source = Image.open(SOURCE).convert("RGB")
hero = ImageOps.fit(source, (WIDTH, HEIGHT - 112), method=Image.Resampling.LANCZOS, centering=(0.56, 0.45))
canvas = Image.new("RGB", (WIDTH, HEIGHT), IVORY)
canvas.paste(hero, (0, 112))

overlay = Image.new("RGBA", hero.size, (0, 0, 0, 0))
odraw = ImageDraw.Draw(overlay)
for x in range(WIDTH):
    opacity = int(180 - 115 * (x / WIDTH))
    odraw.line((x, 0, x, hero.height), fill=(7, 25, 21, max(52, opacity)))
canvas.paste(Image.alpha_composite(hero.convert("RGBA"), overlay).convert("RGB"), (0, 112))

draw = ImageDraw.Draw(canvas)
draw.rectangle((0, 0, WIDTH, 32), fill=GREEN)
draw.text((WIDTH / 2, 16), "A LONDON-BASED CURATED HOUSE OF PAKISTANI DESIGN", anchor="mm", fill=IVORY, font=font("arial.ttf", 12), stroke_width=0)

draw.rectangle((0, 32, WIDTH, 112), fill=IVORY)
draw.text((64, 72), "TAABEER", anchor="lm", fill=GREEN, font=font("georgia.ttf", 25))
draw.text((1136, 72), "COLLECTIONS   DISCOVER PAKISTAN   JOURNAL", anchor="rm", fill=GREEN, font=font("arial.ttf", 12))

draw.text((70, 260), "A CURATED HOUSE OF PAKISTANI DESIGN", fill=IVORY, font=font("arialbd.ttf", 14))
draw.multiline_text((70, 310), "Pakistani design,\nthoughtfully curated", fill="white", font=font("georgia.ttf", 70), spacing=8)
draw.multiline_text((74, 570), "Discover heritage craftsmanship and contemporary design,\nthoughtfully curated from Pakistan.", fill=IVORY, font=font("arial.ttf", 20), spacing=10)

draw.rectangle((70, 680, 334, 744), fill=IVORY)
draw.text((202, 712), "EXPLORE THE COLLECTIONS", anchor="mm", fill=GREEN, font=font("arialbd.ttf", 12))
draw.rectangle((350, 680, 530, 744), outline=IVORY, width=1)
draw.text((440, 712), "OUR STORY", anchor="mm", fill=IVORY, font=font("arialbd.ttf", 12))

canvas.save(OUTPUT, optimize=True)
print(OUTPUT)
