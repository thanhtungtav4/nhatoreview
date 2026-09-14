import React from "react";

/* Figma: Frame 2144771281 (261:619) — 880 wide, 320.683px image, 20px gap.
   Thin React wrapper over the `.nh-article-row` classes in
   components/content/content.css. The canonical markup is plain HTML. */
export function Frame2144771281({ image, title, date = "03/03/2026", excerpt, href = "#", className, style, ...rest }) {
  return (
    <a className={"nh-article-row" + (className ? " " + className : "")} href={href} style={style} {...rest}>
      <img className="nh-article-row__image" src={image} alt="" />
      <span className="nh-article-row__body">
        <span className="nh-article-row__title">{title}</span>
        <span className="nh-dateline"><svg><use href="#nh-clock" /></svg><span><strong>Ngày đăng:</strong> {date}</span></span>
        <span className="nh-article-row__excerpt">{excerpt}</span>
      </span>
    </a>
  );
}
