# Creating Your First Sprite Sheet

In this guide, we'll create a working sprite sheet from individual dinosaur images and display them with CSS.

## The dinosaur images

Our example uses **30+ dinosaur characters** in various states:
- Different characters (Diple Hapey, Confused, etc.)
- Evolution stages
- Item interactions
- Attack animations

Each dinosaur will be positioned in our sprite sheet grid.

## Step 1: Combine images into a sprite

### Option A: Using online tools (easiest)

**Free sprite sheet generators:**
- **TexturePacker** (texturepacker.com) — drag & drop, auto-aligns
- **Sprite Sheet Packer** (spritesheet.gik.fr) — browser-based
- **Piskel** (piskelapp.com) — create & sprite in one tool

**Steps:**
1. Upload all dinosaur PNG images
2. Tool automatically arranges them in a grid
3. Generate sprite sheet + coordinates file
4. Download combined PNG + CSS/JSON with positions

### Option B: Using ImageMagick (command line)

```powershell
# Install ImageMagick first
magick montage *.png -tile 5x6 -geometry 200x200+0+0 sprite-sheet.png
```

This creates a 5-column, 6-row grid with 200x200px cells.

### Option C: Manual Photoshop/GIMP

1. Create new document (e.g., 1000x1200px for 5x6 grid of 200x200 images)
2. Import each dinosaur as a layer
3. Position precisely in grid
4. Flatten and export as PNG

## Step 2: Calculate positions

For our example sprite (assuming 5 columns, 200px each):

| Dinosaur | Column | Row | X Position | Y Position |
|----------|--------|-----|-----------|-----------|
| Diple Hapey | 0 | 0 | 0px | 0px |
| Confused | 1 | 0 | 200px | 0px |
| Siste Colourang | 2 | 0 | 400px | 0px |
| Res S L | 3 | 0 | 600px | 0px |
| Bes Civing | 4 | 0 | 800px | 0px |
| ... | ... | ... | ... | ... |

> **Tip:** Tools like TexturePacker automatically generate these positions in a JSON/XML file.

## Step 3: Write HTML and CSS

### HTML

```html
<div class="sprite dino-diple-hapey"></div>
<div class="sprite dino-confused"></div>
<div class="sprite dino-siste"></div>
```

### CSS

```css
/* Base sprite styles */
.sprite {
  display: inline-block;
  width: 200px;
  height: 200px;
  background-image: url('/attachments/dinosaurs-sprite.png');
  background-repeat: no-repeat;
  background-size: 1000px 1200px; /* Total sprite dimensions */
}

/* Individual dinosaur positions */
.dino-diple-hapey {
  background-position: 0px 0px;
}

.dino-confused {
  background-position: -200px 0px;
}

.dino-siste {
  background-position: -400px 0px;
}

.dino-res {
  background-position: -600px 0px;
}

.dino-bes {
  background-position: -800px 0px;
}
```

> **Important:** Use **negative values** for `background-position` when moving left/up in the sprite!

## Step 4: Test your sprite

Create a simple HTML file:

```html
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="sprite-styles.css">
</head>
<body>
  <h1>Dinosaur Sprites</h1>
  <div class="sprite dino-diple-hapey"></div>
  <div class="sprite dino-confused"></div>
  <div class="sprite dino-siste"></div>
</body>
</html>
```

Open in browser — you should see individual dinosaurs!

## Common issues

### Issue: Only seeing part of one dinosaur

**Fix:** Check that:
- `width` and `height` match individual image size
- `background-size` matches total sprite dimensions
- `background-position` values are negative
- Image URL is correct

### Issue: Seeing the entire sprite

**Fix:** 
- Make sure container is smaller than sprite
- Check `width`/`height` CSS

### Issue: Dinosaurs appear cut off

**Fix:**
- Verify `background-position` values match your grid
- Adjust `background-size` if sprite dimensions are different

**Next:** [03-animations-and-effects.md](03-animations-and-effects.md)
