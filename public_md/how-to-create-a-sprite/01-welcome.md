# How to Create a CSS Sprite Sheet

A **CSS sprite sheet** is a technique where you combine multiple images into a single file and use CSS `background-position` to display different sections. This reduces HTTP requests and improves page performance.

## What you'll learn

- What sprite sheets are and why they're useful
- How to create a sprite sheet from individual images
- CSS techniques to display specific sections
- Interactive examples with dinosaurs
- Performance benefits and best practices

## Why use sprite sheets?

| Benefit | Explanation |
|---------|-------------|
| **Fewer HTTP requests** | One image instead of many = faster loading |
| **Smaller total size** | Combined + compressed often smaller than sum of parts |
| **Smoother animations** | Combine animation frames in one sprite |
| **Easy state changes** | Hover/active states without extra requests |
| **Browser caching** | Single file cached, reused across pages |

## How it works

1. **Combine images** into one large PNG/JPG
2. **Calculate positions** of each image within the sprite
3. **Use CSS `background-image`** to load the sprite
4. **Set `background-position`** to show specific sections
5. **Size container** to show only one section

> **Note:** Modern alternatives include SVG sprites and CSS Grid, but PNG sprites remain popular for performance.

**Let's start →** [02-creating-your-sprite.md](02-creating-your-sprite.md)
