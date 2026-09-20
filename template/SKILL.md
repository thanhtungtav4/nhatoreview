---
name: nhato-design
description: Use this skill to generate well-branded interfaces and assets for NHATO Collection, either for production or throwaway prototypes/mocks/etc. Contains essential design guidelines, colors, type, fonts, assets, and UI kit components for protoyping.
user-invocable: true
---

Read the README.md file within this skill, and explore the other available files.
If creating visual artifacts (slides, mocks, throwaway prototypes, etc), copy assets out and create static HTML files for the user to view. If working on production code, you can copy assets and read the rules here to become an expert in designing with this brand.
If the user invokes this skill without any other guidance, ask them what they want to build or design, ask some questions, and act as an expert designer who outputs HTML artifacts _or_ production code, depending on the need.

Note: this system is plain HTML, CSS and vanilla JS — no React, no build step. Link
`assets/css/nhato.css`, load `assets/icons/nhato-icons.js` as the first element in `<body>`, and
compose with the `nh-*` classes listed in readme.md.

## Continuing the Figma → HTML conversion

This kit was built from the Figma file "NHATO 3.fig" (1 page, 10 frames). All ten frames
are already recreated in `ui_kits/nhato-web/` — see that folder's README.md for the
frame-to-file map with node ids.

To convert further frames or re-sync a changed one:

1. **Get design context.** A `.fig` binary is not readable as text. Connect the Figma MCP
   server (or use the REST API with a personal access token) and pull the frame's node
   tree — layer names, auto-layout gap/padding, geometry, colours, text content. Work from
   that tree, not from a screenshot: screenshots lose every exact value.
2. **Copy values verbatim.** The file uses off-grid numbers on purpose — 13px gaps, 0.924px
   strokes, 320.683px images, `rgba(30,30,30,0.2)` ghost type, `borderRadius: 333.33`. Do
   not round to a 4/8px grid.
3. **Map to existing classes first.** Most new frames reuse what is already here:
   `.nh-hero`, `.nh-rule-head`, `.nh-intro`, `.nh-cta`, `.nh-post-card`, `.nh-art-feature`,
   `.nh-style-tile`, `.nh-service-card`, `.nh-contact-band`. Add a new class only when the
   pattern genuinely has no counterpart, and put it in the matching
   `assets/css/` with a comment naming the Figma node it came from.
4. **Copy the real bitmaps.** Every `url(./assets/<hash>.<ext>)` in the node tree names an
   exact export. Copy that file; never redraw a photo, logo or brand mark as SVG. Downscale
   to the size the layout renders (the raw exports run to 4096px / 7 MB).
5. **Register the screen.** Add `<!-- @dsCard group="NHATO Website" viewport="1440x900"
   name="…" subtitle="…" -->` as line 1, mount the shared chrome with
   `<div data-nh-mount="header"></div>` / `…="footer"`, and load `./chrome.js` plus
   `../../assets/js/nhato.js` at the end of `<body>`.
6. **Update the docs.** Add the new row to `ui_kits/nhato-web/README.md`'s frame map and
   refresh `## Last sync` in `github.md`.

Icons live in one sprite: `assets/icons/nhato-icons.svg`, injected by
`assets/icons/nhato-icons.js`, referenced as `<svg><use href="#nh-arrow-right"></use></svg>`.
Add new glyphs to the `.svg` and re-embed the flattened markup into the `.js` loader.

