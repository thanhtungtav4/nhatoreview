/** One glyph from the NHATO sprite. Colour comes from `currentColor`.
 */
export interface IconProps {
  /** Sprite id without the `nh-` prefix, e.g. "arrow-down". See ICON_NAMES. */
  name: "facebook" | "tiktok" | "youtube" | "x" | "arrow-right" | "arrow-down" | "menu" | "eye" | "calendar" | "clock" | "house" | "apartment" | "villa" | "office" | "commercial" | "listen" | "spark" | "build" | "schedule" | "handover";
  /** Box size in px. 20 in the header, 48 for category tiles, 36 for process steps. */
  size?: number;
  className?: string;
  style?: React.CSSProperties;
}
export declare function Icon(props: IconProps): JSX.Element;
export declare const iconNames: string[];

/** Kit alias for the Figma component `vuesax/linear/arrow-down` (22:360) — renders the arrow-down glyph. */
export declare function VuesaxLinearArrowDown(props: Omit<IconProps, "name">): JSX.Element;
