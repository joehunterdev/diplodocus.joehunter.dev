# Animations & Effects with Sprites

Sprite sheets excel at animations — change `background-position` to show different frames.

## Frame-by-frame animation

### HTML

```html
<div class="sprite sprite-walk"></div>
```

### CSS Animation

```css
.sprite {
  width: 200px;
  height: 200px;
  background-image: url('/attachments/dinosaurs-sprite.png');
  background-size: 1000px 1200px;
}

/* Walking animation: 4 frames */
@keyframes walk {
  0% { background-position: 0px 0px; }        /* Frame 1 */
  25% { background-position: -200px 0px; }    /* Frame 2 */
  50% { background-position: -400px 0px; }    /* Frame 3 */
  75% { background-position: -600px 0px; }    /* Frame 4 */
  100% { background-position: 0px 0px; }      /* Back to start */
}

.sprite-walk {
  animation: walk 0.8s steps(4, end) infinite;
}
```

> **Key:** `steps(4, end)` creates **frame-by-frame** animation instead of smooth transitions.

## Hover effects

### Change on hover

```css
.sprite-dino {
  background-position: 0px 0px;
  transition: background-position 0.1s ease;
}

.sprite-dino:hover {
  background-position: -200px 0px; /* Show different emotion */
}
```

### Multiple states

```css
/* Normal state */
.dino-button {
  background-position: 0px 0px;
}

/* Hover state */
.dino-button:hover {
  background-position: -200px 0px;
}

/* Active/Pressed state */
.dino-button:active {
  background-position: -400px 0px;
}

/* Disabled state */
.dino-button:disabled {
  background-position: -600px 0px;
  opacity: 0.5;
}
```

## Interactive switching

### JavaScript to change sprites

```html
<div id="dino" class="sprite sprite-happy"></div>
<button onclick="changeDino('happy')">Happy</button>
<button onclick="changeDino('confused')">Confused</button>
<button onclick="changeDino('sad')">Sad</button>
```

```javascript
function changeDino(emotion) {
  const dino = document.getElementById('dino');
  dino.className = 'sprite sprite-' + emotion;
}
```

### CSS classes for each emotion

```css
.sprite-happy {
  background-position: 0px 0px;
}

.sprite-confused {
  background-position: -200px 0px;
}

.sprite-sad {
  background-position: -400px 0px;
}
```

## Performance tips

| Technique | Benefit |
|-----------|---------|
| **Use sprites** | Fewer HTTP requests |
| **Compress PNG** | Use TinyPNG or similar |
| **Use `will-change`** | Hints browser to optimize |
| **Avoid excessive animation** | CPU/battery impact |
| **Test on mobile** | Animations strain battery |

### Optimization example

```css
.sprite-animated {
  will-change: background-position;
  animation: walk 0.8s steps(4) infinite;
}

/* Stop animation when not visible */
@media (prefers-reduced-motion) {
  .sprite-animated {
    animation: none;
  }
}
```

> **Accessibility:** Respect `prefers-reduced-motion` for users sensitive to animation.

## Browser support

| Feature | Support |
|---------|---------|
| `background-position` | ✅ All browsers |
| `@keyframes` animation | ✅ IE10+ |
| `steps()` timing | ✅ IE10+ |
| `will-change` | ✅ Modern browsers |

**Next:** [04-best-practices.md](04-best-practices.md)
