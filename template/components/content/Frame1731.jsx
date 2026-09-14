import React from "react";

/* Figma: Frame 1731 (261:521) — 280×268, a 180px image over a Manrope SemiBold 16 title.
   Thin React wrapper over `.nh-thumb-card`. */
export function Frame1731({ image, title, href = "#", className, style, ...rest }) {
  return (
    <a className={"nh-thumb-card" + (className ? " " + className : "")} href={href} style={style} {...rest}>
      <img src={image} alt="" />
      <span className="nh-thumb-card__title">{title}</span>
    </a>
  );
}
