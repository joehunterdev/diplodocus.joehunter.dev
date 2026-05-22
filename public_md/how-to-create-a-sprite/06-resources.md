# Resources & Tools

## Tools for creating sprites

### Online (browser-based)

| Tool | URL | Notes |
|------|-----|-------|
| **Sprite Sheet Packer** | spritesheet.gik.fr | Free, no login required |
| **Piskel** | piskelapp.com | Pixel art editor + sprite creation |
| **SpriteDancer** | spritedancer.com | AI-powered sprite animation |
| **Responsive Sprites** | responsive.imgix.com | Cloud-based sprite optimization |

### Desktop applications

| Tool | Platform | Cost | Best For |
|------|----------|------|----------|
| **TexturePacker** | Win/Mac | Free-$$$ | Game development, multiple export formats |
| **Aseprite** | Win/Mac/Linux | $$ | Pixel art + sprite animation |
| **GIMP** | Win/Mac/Linux | Free | Manual sprite creation |
| **Photoshop** | Win/Mac | $$$ | Professional, all features |
| **Krita** | Win/Mac/Linux | Free | Digital painting + sprite sheets |

### Command-line tools

```bash
# ImageMagick - arrange images in grid
magick montage *.png -tile 5x6 -geometry 200x200+0+0 sprite.png

# GraphicsMagick (faster ImageMagick)
gm montage *.png -tile 5x6 -geometry 200x200+0+0 sprite.png

# OptiPNG - compress resulting sprite
optipng -o2 sprite.png

# PNGQuant - reduce colors (smaller file)
pngquant --quality 70-90 sprite.png
```

## Code examples & snippets

### GitHub repos

- **Sprite.js** — javascript sprite library
- **CSS Sprites Generator** — CLI tool
- **SpriteLib** — Python sprite sheet generator

### CodePen examples

Search "CSS sprite" for hundreds of working examples:
- Sprite animations
- Interactive sprite galleries
- Game-style character sheets
- Button state sprites

## Performance optimization

### Compression tools

| Tool | Type | File Reduction |
|------|------|----------------|
| **TinyPNG** | Online | 20-80% for photos |
| **PNGQuant** | CLI | 30-60% for graphics |
| **ImageOptim** | Mac Desktop | 10-30% for PNGs |
| **FileOptimizer** | Windows | Various formats |

### Using compressed sprites

```bash
# Example workflow
magick montage *.png -tile 5x6 -geometry 200x200+0+0 sprite-raw.png
pngquant --quality 75-90 sprite-raw.png --output sprite.png
optipng -o2 sprite.png
```

## CSS sprite generators

### Automatic CSS generation tools

- **SpritePad** — Generate CSS from sprite coordinates
- **Compass/Sass** — `@each` loop to generate sprite classes
- **PostCSS Sprites** — Automated sprite creation pipeline

### Manual generator (HTML form)

```html
<form id="sprite-calculator">
  <input type="number" id="cols" placeholder="Columns" value="5">
  <input type="number" id="rows" placeholder="Rows" value="6">
  <input type="number" id="width" placeholder="Image width" value="200">
  <input type="number" id="height" placeholder="Image height" value="200">
  <button type="submit">Generate CSS</button>
</form>

<textarea id="output"></textarea>

<script>
document.getElementById('sprite-calculator').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const cols = parseInt(document.getElementById('cols').value);
  const rows = parseInt(document.getElementById('rows').value);
  const width = parseInt(document.getElementById('width').value);
  const height = parseInt(document.getElementById('height').value);
  
  let css = '/* Generated sprite positions */\n\n';
  
  for (let r = 0; r < rows; r++) {
    for (let c = 0; c < cols; c++) {
      const x = -c * width;
      const y = -r * height;
      css += `.sprite-${r}-${c} { background-position: ${x}px ${y}px; }\n`;
    }
  }
  
  document.getElementById('output').value = css;
});
</script>
```

## Learning resources

### Tutorials

- **CSS Tricks: CSS Sprites** — Comprehensive guide
- **Treehouse: Sprite Sheets** — Video course
- **Udemy: Game Sprite Creation** — Interactive course

### Browser DevTools tips

1. **Inspect sprite in DevTools:**
   - Right-click → Inspect
   - Open `background-image` URL in new tab
   - See full sprite sheet
   - Hover over elements to verify positions

2. **Debug animations:**
   - Check computed styles
   - Verify `background-position` values
   - Slow down animation in DevTools

3. **Performance profiling:**
   - Measure rendering time
   - Check paint regions
   - Monitor FPS during animation

## Troubleshooting guides

### Common issues & solutions

**Issue:** Sprite doesn't appear
- Check URL path is correct
- Verify image exists
- Inspect Network tab for 404s

**Issue:** Wrong part of sprite showing
- Use Chrome DevTools to inspect `background-position`
- Recalculate coordinates
- Check sprite sheet orientation

**Issue:** Blurry sprites on retina
- Provide 2x resolution sprite
- Use `image-rendering: pixelated`
- Check DPI in media query

## Best practices checklist

- ✅ Combine frequently-used images only
- ✅ Group by purpose (characters, icons, etc.)
- ✅ Document all positions
- ✅ Compress before deployment
- ✅ Test across browsers
- ✅ Provide fallback for older browsers
- ✅ Consider SVG for icons
- ✅ Monitor performance

## Further reading

- **MDN: background-position** — CSS reference
- **Web.dev: CSS Sprites** — Performance tips
- **Canvas vs Sprites** — When to use each
- **Modern Image Formats** — WebP, AVIF alternatives

---

*Last updated: May 2026*  
*How to Create a CSS Sprite Sheet v1.0*
