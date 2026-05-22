# Interactive Example: Dinosaur Sprite Gallery

A working example showing dinosaur sprites in action.

## The sprite sheet

Our example uses a **1000x1200px sprite sheet** with 30 dinosaur characters in a **5-column, 6-row grid**. Each dinosaur is **200px × 200px**.

## HTML setup

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dinosaur Sprite Gallery</title>
  <link rel="stylesheet" href="dinosaur-sprites.css">
</head>
<body>
  <h1>🦕 Dinosaur Sprite Gallery</h1>
  <p>Click a dinosaur to see it in detail</p>

  <div class="gallery">
    <div class="sprite dino-diple-hapey" data-name="Diple Hapey"></div>
    <div class="sprite dino-confused" data-name="Confused"></div>
    <div class="sprite dino-siste" data-name="Siste Colourang"></div>
    <div class="sprite dino-res" data-name="Res S L"></div>
    <div class="sprite dino-bes-civing" data-name="Bes Civing"></div>
    
    <div class="sprite dino-pierra" data-name="Pierra Flying"></div>
    <div class="sprite dino-veles" data-name="Veles Floser"></div>
    <div class="sprite dino-cteop" data-name="Cteop Duel"></div>
    <!-- Add more dinosaurs here -->
  </div>

  <div class="detail-view">
    <h2 id="dino-name">Select a dinosaur</h2>
    <div id="dino-display" class="sprite-large dino-diple-hapey"></div>
  </div>

  <script src="dinosaur-sprites.js"></script>
</body>
</html>
```

## CSS styles

```css
/* ========== Base Sprite Styles ========== */
.sprite {
  display: inline-block;
  width: 200px;
  height: 200px;
  background-image: url('/attachments/dinosaurs-sprite.png');
  background-repeat: no-repeat;
  background-size: 1000px 1200px;
  cursor: pointer;
  transition: transform 0.2s ease;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.sprite:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* ========== Individual Dinosaur Positions ========== */
/* Row 0 */
.dino-diple-hapey { background-position: 0px 0px; }
.dino-confused { background-position: -200px 0px; }
.dino-siste { background-position: -400px 0px; }
.dino-res { background-position: -600px 0px; }
.dino-bes-civing { background-position: -800px 0px; }

/* Row 1 */
.dino-pierra { background-position: 0px -200px; }
.dino-veles { background-position: -200px -200px; }
.dino-cteop { background-position: -400px -200px; }
.dino-cteop-due { background-position: -600px -200px; }
/* Add remaining positions... */

/* ========== Gallery Layout ========== */
.gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, 200px);
  gap: 16px;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  margin-bottom: 30px;
}

/* ========== Detail View ========== */
.detail-view {
  background: white;
  border: 2px solid #667eea;
  border-radius: 12px;
  padding: 30px;
  text-align: center;
}

.detail-view h2 {
  color: #333;
  margin-bottom: 20px;
}

.sprite-large {
  width: 300px;
  height: 300px;
  background-size: 1500px 1800px;
  margin: 0 auto;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Scale positions for larger display */
.sprite-large.dino-diple-hapey { background-position: 0px 0px; }
.sprite-large.dino-confused { background-position: -300px 0px; }
.sprite-large.dino-siste { background-position: -600px 0px; }
/* (Scale all positions by 1.5x) */
```

## JavaScript interactivity

```javascript
// Get all sprite elements
const sprites = document.querySelectorAll('.sprite');
const dinoName = document.getElementById('dino-name');
const dinoDisplay = document.getElementById('dino-display');

// Add click listener to each sprite
sprites.forEach(sprite => {
  sprite.addEventListener('click', function() {
    // Get dinosaur name from data attribute
    const name = this.getAttribute('data-name');
    
    // Update display
    dinoName.textContent = name;
    
    // Get all classes and find the dinosaur class
    const dinoClass = Array.from(this.classList).find(cls => cls.startsWith('dino-'));
    
    // Update large display
    dinoDisplay.className = 'sprite-large ' + dinoClass;
  });
});
```

## Animation example

Add walking animation:

```css
/* Sprite sheet has 4 walking frames in a row */
@keyframes walk-animation {
  0% { background-position: 0px 400px; }
  25% { background-position: -200px 400px; }
  50% { background-position: -400px 400px; }
  75% { background-position: -600px 400px; }
  100% { background-position: 0px 400px; }
}

.sprite-walking {
  animation: walk-animation 0.6s steps(4) infinite;
}
```

## Using the example

1. **Save CSS** as `dinosaur-sprites.css`
2. **Save JS** as `dinosaur-sprites.js`
3. **Place sprite image** at `/attachments/dinosaurs-sprite.png`
4. **Open HTML** in browser
5. **Click dinosaurs** to see details

## Expected output

✅ Grid gallery of dinosaur thumbnails  
✅ Hover effect zooms slightly  
✅ Click to see larger version with name  
✅ Smooth transitions and shadows  
✅ Mobile responsive

## Customization

### Add more dinosaurs

```html
<div class="sprite dino-new-dino" data-name="New Dinosaur"></div>
```

```css
/* Calculate position based on grid */
.dino-new-dino { background-position: -600px -400px; } /* Column 3, Row 2 */
```

### Adjust grid layout

```css
.gallery {
  grid-template-columns: repeat(3, 200px); /* Fixed 3 columns */
  /* or */
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Responsive */
}
```

### Change sprite sheet dimensions

If using a different sprite size:

```css
.sprite {
  width: 256px; /* New size */
  height: 256px;
  background-size: 1280px 1536px; /* Scale accordingly */
}

.dino-diple-hapey { background-position: 0px 0px; }
.dino-confused { background-position: -256px 0px; } /* Use new width */
```

**Next:** [06-resources.md](06-resources.md)
