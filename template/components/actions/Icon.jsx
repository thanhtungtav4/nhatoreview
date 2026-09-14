import React from "react";

/* The NHATO icon set. Every glyph lives in assets/icons/nhato-icons.svg and is
   inlined by assets/icons/nhato-icons.js, which must run as the first element in
   <body>. `vuesax/linear/arrow-down` (Figma 22:360) is `name="arrow-down"`. */
export const iconNames = ["facebook", "tiktok", "youtube", "x", "arrow-right", "arrow-down", "menu", "eye", "calendar", "clock", "house", "apartment", "villa", "office", "commercial", "listen", "spark", "build", "schedule", "handover"];

export function Icon({ name, size = 20, className, style, ...rest }) {
  return (
    <svg className={className} style={{ width: size, height: size, flex: "0 0 auto", ...style }} aria-hidden="true" {...rest}>
      <use href={"#nh-" + name} />
    </svg>
  );
}

/* Kit alias — the Figma file names this component `vuesax/linear/arrow-down` (22:360). */
export const VuesaxLinearArrowDown = (props) => <Icon name="arrow-down" {...props} />;
