Renders one glyph from the NHATO sprite — use it anywhere an icon is needed; never paste raw SVG.

```jsx
<Icon name="arrow-right" size={16} />
<Icon name="villa" size={48} style={{ color: "var(--nh-gold)" }} />
```

The sprite must be inlined first: `<script src="assets/icons/nhato-icons.js"></script>` as the first element in `<body>`. In plain HTML write `<svg class="nh-icon"><use href="#nh-arrow-right"></use></svg>` instead.
