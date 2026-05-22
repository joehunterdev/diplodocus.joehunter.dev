# Best Practices & Alternatives

## When to use sprites

### ✅ Good use cases

- **Game assets** — characters, tiles, animations
- **Icon sets** — dozens of small icons
- **UI animations** — button states, loading spinners
- **Mobile games** — reduced bandwidth crucial
- **High-traffic sites** — performance critical

### ❌ Poor use cases

- **Single large images** — use individual files
- **Rarely-used images** — don't cache well together
- **Highly variable sizes** — wastes space
- **SVG icons** — use SVG sprites instead
- **Always-visible hero images** — individual PNG better

## Modern alternatives

### SVG Sprites

Better for icons and scalable graphics:

```html
<svg>
  <use href="icons.svg#icon-user"></use>
  <use href="icons.svg#icon-settings"></use>
</svg>
```

**Advantages:**
- Scalable (no pixelation)
- Can style with CSS
- Smaller file size for icons
- Accessibility-friendly

### CSS Grid & CSS-in-JS

For complex layouts:

```css
/* Grid approach — positions computed automatically */
.sprite-grid {
  display: grid;
  grid-template-columns: repeat(5, 200px);
  gap: 0;
}

.sprite-grid-item {
  width: 200px;
  height: 200px;
  background-image: url('sprite.png');
  background-size: 1000px 1200px;
}
```

### Image Maps (legacy, avoid)

❌ **Not recommended** — use sprites or SVG instead.

```html
<!-- Old approach — don't use! -->
<img src="sprite.png" usemap="#map">
<map name="map">
  <area shape="rect" coords="0,0,200,200" href="/dino/1">
</map>
```

## Optimization checklist

Before deploying sprites:

- ✅ **Compress PNG** — TinyPNG, PNGQuant
- ✅ **Use correct dimensions** — match container exactly
- ✅ **Group related images** — don't mix unrelated sprites
- ✅ **Document positions** — maintain coordinate list
- ✅ **Test across browsers** — especially older IE
- ✅ **Consider retina displays** — 2x resolution sprites
- ✅ **Lazy-load if possible** — defer non-critical sprites
- ✅ **Monitor performance** — use DevTools

## Retina displays (@2x sprites)

For high-DPI screens, provide 2x resolution:

```css
.sprite {
  width: 200px;
  height: 200px;
  background-image: url('sprite.png');
  background-size: 1000px 1200px;
}

@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .sprite {
    background-image: url('sprite@2x.png');
    background-size: 2000px 2400px;
  }
}
```

## Tools for sprite management

| Tool | Type | Best For |
|------|------|----------|
| **TexturePacker** | Desktop/Online | Professional games, export formats |
| **Sprite Sheet Packer** | Online | Quick browser-based sprites |
| **Piskel** | Online | Pixel art creation + sprite |
| **ImageMagick** | CLI | Batch processing, automation |
| **GIMP** | Desktop | Manual control, free |
| **Photoshop** | Desktop | Professional, all features |

## Troubleshooting

### Sprites look blurry

**Cause:** Background not positioned on pixel boundary  
**Fix:**
```css
.sprite {
  image-rendering: pixelated; /* Pixel art */
  background-position: 0px 0px; /* Whole numbers only */
}
```

### Animations stutter

**Cause:** Browser rendering lag  
**Fix:**
```css
.sprite-animated {
  will-change: background-position;
  transform: translate3d(0, 0, 0); /* Force GPU rendering */
}
```

### File size too large

**Cause:** Uncompressed PNG, too many images  
**Fix:**
- Use PNG quantization (reduce colors)
- Group only frequently-used images
- Consider WebP format

### Sprites don't align

**Cause:** Off-by-one errors in positioning  
**Fix:**
- Use grid: `background-position: calc(-1 * var(--col) * 200px) calc(-1 * var(--row) * 200px);`
- Generate CSS from sprite tool

**Next:** [05-interactive-example.md](05-interactive-example.md)
