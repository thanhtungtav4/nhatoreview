#!/usr/bin/env python3
"""Conservative CSS minifier for scripts/build-asset-bundles.sh.

Deliberately does NOT use a real CSS parser (lightningcss/esbuild were tried
and tried-and-rejected: lightningcss 1.0.0-alpha.72 rewrites
`:not(.x)::before` into two selectors, one of them missing the pseudo-
element — a real semantic bug, not safe for production). This script only
removes bytes that are insignificant whitespace or comments per the CSS
grammar. It never rewrites a selector, merges a rule, changes a value, or
touches anything inside a string/url() literal. Byte-for-byte, the only
things removed are: /* comments */, redundant whitespace outside strings,
and the semicolon immediately before a closing `}` (always optional in CSS).

Usage: minify-css.py <input-file> <output-file>
"""
import sys


def minify(css: str) -> str:
    out = []
    i = 0
    n = len(css)
    # Trailing whitespace pending before the next non-space token; collapsed
    # to a single space only when a space is actually needed to keep two
    # tokens from merging into one (e.g. `and(min-width` must stay
    # `and (min-width`).
    pending_space = False
    last_out_char = ""

    def emit(ch: str):
        nonlocal last_out_char
        out.append(ch)
        last_out_char = ch

    while i < n:
        ch = css[i]

        # /* comment */ — strip entirely (CSS comments cannot be nested or
        # contain "*/", and never open inside a string per the grammar).
        if ch == "/" and i + 1 < n and css[i + 1] == "*":
            end = css.find("*/", i + 2)
            i = (end + 2) if end != -1 else n
            pending_space = True
            continue

        # String literal — copy verbatim, untouched, including whitespace.
        if ch in ("'", '"'):
            quote = ch
            j = i + 1
            while j < n:
                if css[j] == "\\" and j + 1 < n:
                    j += 2
                    continue
                if css[j] == quote:
                    j += 1
                    break
                j += 1
            if pending_space and last_out_char and last_out_char not in "{};,:>(":
                emit(" ")
            pending_space = False
            for c in css[i:j]:
                emit(c)
            i = j
            continue

        # Whitespace run — collapse, defer emission until we know whether
        # it's actually needed (structural chars never need a preceding
        # space).
        if ch in " \t\r\n\f":
            j = i
            while j < n and css[j] in " \t\r\n\f":
                j += 1
            pending_space = True
            i = j
            continue

        # Structural characters never need a preceding space, and never
        # need the pending space emitted before them.
        if ch in "{};,":
            pending_space = False
            # Optional trailing semicolon right before `}` is redundant.
            if ch == ";":
                k = i + 1
                while k < n and css[k] in " \t\r\n\f":
                    k += 1
                if k < n and css[k] == "}":
                    i = k
                    continue
            emit(ch)
            i += 1
            pending_space = False
            continue

        # Any other character: flush a single pending space first (only if
        # actually needed to avoid merging two tokens — never after an
        # opening structural char).
        if pending_space and last_out_char and last_out_char not in "{};,:>(":
            emit(" ")
        pending_space = False
        emit(ch)
        i += 1

    return "".join(out).strip()


def main() -> None:
    if len(sys.argv) != 3:
        print("usage: minify-css.py <input-file> <output-file>", file=sys.stderr)
        raise SystemExit(2)
    src_path, dst_path = sys.argv[1], sys.argv[2]
    with open(src_path, "r", encoding="utf-8") as f:
        css = f.read()
    minified = minify(css)
    with open(dst_path, "w", encoding="utf-8") as f:
        f.write(minified)


if __name__ == "__main__":
    main()
