import { createRequire } from "node:module";

const require = createRequire("/Users/macbook/.codex/skills/package.json");
const { chromium } = require("playwright");

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 1100 } });
await page.goto("http://127.0.0.1:4173/", { waitUntil: "networkidle" });
await page.waitForTimeout(500);
await page.mouse.wheel(0, 3000);
await page.waitForTimeout(150);
const result = await page.evaluate(() => {
  const section = document.querySelector(".home-values");
  const image = document.querySelector(".home-values__image");
  const content = document.querySelector(".home-values__content");
  const imageStyle = getComputedStyle(image);
  const sectionStyle = getComputedStyle(section);
  return {
    scrollY: window.scrollY,
    header: { rect: document.querySelector(".site-header").getBoundingClientRect().toJSON(), position: getComputedStyle(document.querySelector(".site-header")).position, classes: document.querySelector(".site-header").className },
    viewport: { width: window.innerWidth, height: window.innerHeight },
    section: { rect: section.getBoundingClientRect().toJSON(), display: sectionStyle.display, gridTemplateColumns: sectionStyle.gridTemplateColumns },
    image: { rect: image.getBoundingClientRect().toJSON(), position: imageStyle.position, inset: imageStyle.inset, width: imageStyle.width, height: imageStyle.height, zIndex: imageStyle.zIndex },
    content: { rect: content.getBoundingClientRect().toJSON(), display: getComputedStyle(content).display, gridColumn: getComputedStyle(content).gridColumn, maxWidth: getComputedStyle(content).maxWidth },
    intro: document.querySelector(".home-values__intro").getBoundingClientRect().toJSON(),
    process: document.querySelector(".home-process-list").getBoundingClientRect().toJSON(),
    steps: [...document.querySelectorAll(".home-process-step")].map((step) => step.getBoundingClientRect().toJSON()),
    stylesheets: [...document.styleSheets].map((sheet) => sheet.href),
  };
});
console.log(JSON.stringify(result, null, 2));
await browser.close();
