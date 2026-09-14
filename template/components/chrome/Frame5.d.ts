/** Site header — the gold wordmark, five-item nav and the Call us plate, over a dark scrim.
 * Figma component `Frame 5` (277:2486) — the file leaves its components auto-named,
 * so the export keeps the Figma name. Readable alias in readme.md: "SiteHeader".
 */
export interface Frame5Props {
  /** Nav items, left to right. Defaults to Network / Home / Art / Taste / Original. */
  items?: Array<{ key: string; label: string; href: string }>;
  /** `key` of the item to mark aria-current. */
  current?: string;
  /** Displayed as "Call us: <phone>". */
  phone?: string;
  /** Path to the gold NHATO wordmark PNG, relative to the consuming page. */
  logoSrc?: string;
  /** Social glyph ids from the sprite. Header shows three; the footer adds "x". */
  social?: Array<"facebook" | "tiktok" | "youtube" | "x">;
  /** Opaque scrolled state instead of the transparent gradient. */
  scrolled?: boolean;
  className?: string;
  style?: React.CSSProperties;
}
export declare function Frame5(props: Frame5Props): JSX.Element;

