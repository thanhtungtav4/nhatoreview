/** Compact sidebar row: 94px square thumb, dateline and an optional gold chip.
 * Figma component `Frame 2144771605` (268:1997) — the file leaves its components auto-named,
 * so the export keeps the Figma name. Readable alias in readme.md: "ArticleCompact".
 */
export interface Frame2144771605Props {
  image: string;
  title: string;
  date?: string;
  chip?: string;
  href?: string;
  className?: string;
  style?: React.CSSProperties;
}
export declare function Frame2144771605(props: Frame2144771605Props): JSX.Element;

