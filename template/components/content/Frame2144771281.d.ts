/** Wide editorial row: image left, title, dateline and excerpt right.
 * Figma component `Frame 2144771281` (261:619) — the file leaves its components auto-named,
 * so the export keeps the Figma name. Readable alias in readme.md: "ArticleRow".
 */
export interface Frame2144771281Props {
  /** Path to the article image. Rendered at 320.683px wide, stretched to row height. */
  image: string;
  title: string;
  /** Printed after the bold "Ngày đăng:" label. */
  date?: string;
  excerpt?: string;
  href?: string;
  className?: string;
  style?: React.CSSProperties;
}
export declare function Frame2144771281(props: Frame2144771281Props): JSX.Element;

