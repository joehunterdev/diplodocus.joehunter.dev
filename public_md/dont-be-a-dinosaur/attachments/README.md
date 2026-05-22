# Don't Be a Dinosaur — Assets

This folder contains all visual assets for the "Don't Be a Dinosaur" documentation section.

All images generated via AI (ChatGPT DALL-E, Midjourney, etc.) following the prompt specifications in `.docs/dont-be-a-dinosaur/asset-prompts.md`.

## Naming Convention

```
{page-number}{letter}-{descriptor}.{ext}

Examples:
00a-hero.png          → Welcome page hero image
01a-hero.png          → React lifecycle hero
01b-egg-model.svg     → Egg diagram for lifecycle
01c-render-commit.svg → Render vs commit phase diagram
```

## Dimensions

- **Hero images:** 1200×400px (full-width banners)
- **Inline diagrams:** 800×600px (content-width illustrations)
- **Character assets:** 200×200px (reusable dino characters)

## Format Priority

1. **SVG** for diagrams, characters, and flow charts (editable, scalable)
2. **PNG** for complex illustrations or raster-only scenes (transparent backgrounds)
3. **JPG** only if file size is critical and transparency not needed
