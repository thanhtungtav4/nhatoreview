import React from "react";

/* Figma: Frame 2144771605 (268:1997) — 388 wide, 94px square thumb, optional gold chip.
   Thin React wrapper over `.nh-article-compact`. */
export function Frame2144771605({ image, title, date = "03/03/2026", chip, href = "#", className, style, ...rest }) {
  return (
    <a className={"nh-article-compact" + (className ? " " + className : "")} href={href} style={style} {...rest}>
      <img className="nh-article-compact__thumb" src={image} alt="" />
      <span className="nh-article-compact__body">
        <span className="nh-article-compact__top">
          <span className="nh-dateline"><svg><use href="#nh-clock" /></svg><span><strong>Ngày đăng:</strong> {date}</span></span>
          {chip ? <span className="nh-chip">{chip}</span> : null}
        </span>
        <span className="nh-article-compact__title">{title}</span>
      </span>
    </a>
  );
}
