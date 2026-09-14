import React from "react";

/* Figma: Frame 5 (277:2486) — the site header, 1440×94, 16/72 padding, 64px nav gap.
   Thin React wrapper over the `.nh-header` classes in components/chrome/chrome.css,
   for consumers building in React. The canonical markup is plain HTML — see
   ui_kits/nhato-web/chrome.js. */
const NAV = [
  { key: "network", label: "Network", href: "#" },
  { key: "home", label: "Home", href: "#" },
  { key: "art", label: "Art", href: "#" },
  { key: "taste", label: "Taste", href: "#" },
  { key: "original", label: "Original", href: "#" },
];
const SOCIAL = ["facebook", "tiktok", "youtube"];

export function Frame5({ items = NAV, current = "home", phone = "0968 677 337", logoSrc = "../../assets/logo/nhato-wordmark.png", social = SOCIAL, scrolled = false, className, style, ...rest }) {
  return (
    <header className={"nh-header" + (scrolled ? " is-scrolled" : "") + (className ? " " + className : "")} style={style} {...rest}>
      <div className="nh-header__inner nh-container">
        <a className="nh-brand" href="#" aria-label="NHATO Collection">
          <span className="nh-brand__mark"><img src={logoSrc} alt="NHATO" /></span>
          <span className="nh-brand__sub">COLLECTION</span>
        </a>
        <nav className="nh-header__nav" aria-label="Điều hướng chính">
          <ul className="nh-header__list">
            {items.map((i) => (
              <li className="nh-header__item" key={i.key}>
                <a className="nh-header__link" href={i.href} aria-current={i.key === current ? "page" : undefined}>{i.label}</a>
              </li>
            ))}
          </ul>
        </nav>
        <div className="nh-header__utilities">
          <div className="nh-social nh-social--header" aria-label="Mạng xã hội">
            {social.map((n) => (
              <a className="nh-social__link" href="#" key={n} aria-label={n}>
                <svg className="nh-icon"><use href={"#nh-" + n} /></svg>
              </a>
            ))}
          </div>
          <a className="nh-header__call" href={"tel:" + phone.replace(/\s/g, "")}>Call us: {phone}</a>
          <button className="nh-header__toggle" type="button" aria-expanded="false" aria-label="Mở menu"><span /><span /></button>
        </div>
      </div>
    </header>
  );
}
