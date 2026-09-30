# KM Catalog Builder

A WordPress plugin that gives Elementor everything a product catalogue or WooCommerce shop needs, without
Elementor Pro: a filterable product grid with its own sidebar, a product page built from parts, and the
brand-site pieces around them.

Built for a live client catalogue, then made brand-neutral: no colours, logos or copy are baked in.
Every colour, label and line of text is an Elementor control, and every piece of markup is a template a
theme can override.

## Widgets

| Widget | What you get |
| --- | --- |
| **Products Grid + Filters** | The catalogue: a filter sidebar built from any product taxonomy, tabs, search, sorting, and load more or numbered pages, all over AJAX with no page reload. |
| **Product Gallery** | One large image on a coloured card, with thumbnails below or beside it. |
| **Product Variations** | A box per option; choosing one shows that variation's own image in the gallery. |
| **Product Accordion** | The product's sections as panels, edited per product, with a note under the accordion. |
| **Product Description** | The short or the full description. |
| **Product Taxonomy Line** | The small line above a title, built from whichever taxonomies you choose. |
| **Product Button** | A per-product call to action that can be held back as "Coming Soon" outside chosen categories. |
| **Related Products** | The grid's cards, in a row. |
| **Button, Segmented Switcher, Feature List** | Buttons, linked pills and icon lists for the rest of the site. |
| **Marquee, FAQ, Navbar** | A moving line of text or logos, questions and answers, and a menu bar with a language menu and mobile drawer. |
| **Shape Image, Shape Divider, Wave Edge** | Images masked into shapes and edges between sections. |
| **Container edges** | Wave and scallop edges and a floating animation on any Elementor container, from its own panel. |

## Product pages without Elementor Pro

Without Elementor Pro, WooCommerce draws the product page and Elementor can't touch it. KM Catalog Builder
adds its own product templates under **WooCommerce → Product Templates**: pick one and it is used for every
product. Templates are ordinary posts, so Polylang translates them and each language gets its own layout.
Where Elementor Pro is active this module stays off, so the two don't fight over the page.

## Also included

- **Per-product and per-category card colours**, set under Product data.
- **Custom icons**: drop SVG, WebP, PNG or JPG files into `assets/icons` and they appear in Elementor's icon picker.
- **Theme overrides**: copy any file from `templates/` into `your-theme/km-catalog-builder/` and edit it there.
- **RTL ready**: alignment, arrows and the marquee follow the page direction, so Arabic pages work without a second stylesheet.

## Requirements

- WordPress 5.9+, PHP 7.4+
- Elementor
- WooCommerce 6.0+
- Polylang (optional), for per-language product templates

## Author

Kirollos Magdy — <https://kirollosmagdy.com>

## Licence

Copyright (c) 2026 Kirollos Magdy. All rights reserved. No part of this plugin may be copied, changed or
redistributed without written permission. See [LICENSE](LICENSE).
