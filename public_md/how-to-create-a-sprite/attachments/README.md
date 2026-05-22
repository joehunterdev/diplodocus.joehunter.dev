# Attachments

This folder contains the dinosaur sprite images and supporting assets for the CSS sprite tutorial.

## Files included

- **dinosaurs-sprite.png** — Main 1000x1200px sprite sheet
- **dinosaurs-sprite@2x.png** — High-DPI version for retina displays
- **individual-dinosaurs/** — Original uncompressed dinosaur images

## Using in your project

### Reference the sprite

```css
.sprite {
  background-image: url('/attachments/dinosaurs-sprite.png');
}
```

### For retina displays

```css
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .sprite {
    background-image: url('/attachments/dinosaurs-sprite@2x.png');
    background-size: 2000px 2400px;
  }
}
```

## Dinosaur characters

The sprite sheet includes 30+ dinosaur characters:

1. **Diple Hapey** (0, 0) — Turquoise dinosaur, happy
2. **Confused** (1, 0) — Turquoise dinosaur, confused
3. **Siste Colourang** (2, 0) — Turquoise dinosaur, thoughtful
4. **Res S L** (3, 0) — Red dinosaur, sad
5. **Bes Civing** (4, 0) — Red dinosaur, crying
6. **Pierra Flying** (0, 1) — Purple pteranodon
7. **Veles Floser** (1, 1) — Green dinosaur
8. **Cteop Due** (2, 1) — Blue dinosaur
9. And 20+ more!

See `02-creating-your-sprite.md` for complete position mapping.
