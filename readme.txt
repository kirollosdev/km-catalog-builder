=== KM Catalog Builder ===
Contributors: kirollosmagdy
Author URI: https://kirollosmagdy.com
Plugin URI: https://github.com/kirollosdev/km-catalog-builder
Requires at least: 5.9
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: Proprietary, all rights reserved

Elementor widgets for a catalogue or a shop: a filterable product grid, a
single product page built out of parts, and the pieces a brand site needs
around them.

== What it is ==

The reusable half of a shop built for a client, lifted out so the next one
starts here rather than from nothing. Nothing in it is about that client: no
brand colours, no logos, none of their wording. Every colour, label and piece
of copy is a control in Elementor.

It needs Elementor and WooCommerce.

== The widgets ==

  Products Grid + Filters   the catalogue: a filter sidebar built from any
                            product taxonomy, tabs, search, sorting, load more
                            or numbered pages, over AJAX with no page reload
  Product Gallery           one large image on a coloured card, thumbnails
                            below it or beside it
  Product Variations        a box per option, 2 kg and 10 kg say. Choosing one
                            shows that variation's own image in the gallery
  Product Accordion         the product's sections as panels, edited per
                            product under Product data > Product Details, with
                            a note under the whole accordion
  Product Description       the short or the full description
  Product Taxonomy Line     the small line above a title, built from whichever
                            taxonomies you choose
  Product Button            a call to action a product can point somewhere of
                            its own, and that can be held back as "Coming
                            Soon" for every category but the ones you name
  Related Products          the same cards as the grid, in a row
  Button                    the same button, away from a product page
  Segmented Switcher        two or more linked pills
  Feature List              a list with an icon per line
  Marquee                   a line of text or logos that travels
  FAQ                       questions and answers, on any page
  Navbar                    a menu bar with a language menu and a mobile
                            drawer
  Shape Image               an image masked into a shape
  Shape Divider, Wave Edge  edges between sections
  Container edges           wave and scallop edges on any Elementor container,
                            and a floating animation, both from the
                            container's own panel

== Product pages ==

Without Elementor Pro, WooCommerce draws the product page and Elementor cannot
touch it. This plugin adds its own product template instead: a post type of
layouts under WooCommerce > Product Templates, one of which is chosen under
WooCommerce > Product Template and used for every product.

The layouts are ordinary posts, so Polylang translates them with its own +
button and each language gets its own. A language with no translation falls
back to the chosen one.

Where Elementor Pro is active its Theme Builder does this already, so the
module is off there unless the checkbox on that screen is ticked. Two
templates claiming the same page fight over it, so use one or the other.

== Per product ==

Under Product data:

  Grid Card         two colours, the card ground and the wave that rises on
                    hover, plus a link for the product button
  Product Details   the accordion's sections, and the note under it

A category can carry the same two colours, so a product with none of its own
takes its category's.

== The icon tab ==

Drop images into assets/icons and they appear in Elementor's icon picker under
KM Catalog Builder, for the feature list, the navbar and anywhere else an icon
is chosen. SVG, WebP, PNG and JPG.

== Templates ==

Every piece of markup is a template file, and a theme can override any of them:
copy a file from km-catalog-builder/templates/ into
your-theme/km-catalog-builder/ and edit it there.

== Translating and RTL ==

Every label a visitor reads is a control, so a template in another language
sets its own. Alignment, arrows and the marquee follow the page direction, so
an Arabic page reads right to left without a second stylesheet.

== Changelog ==

= 1.0.0 =
* First release: the reusable widgets, lifted out of a client build and made
  brand neutral. Its prefixes, CSS classes and meta keys are its own, so it can
  sit beside the plugin it came from without either treading on the other.

= 1.1.0 =
* Added: the product template. It was a plugin of its own, and it is a module
  here, since the widgets need a page to sit on and a site without Elementor
  Pro has no way to build one. Off where Elementor Pro is active, which has
  its own Theme Builder, with a checkbox to override that either way.
* Its layouts, settings and post type carry this plugin's own names, so the
  standalone plugin can stay installed on a site that already runs it.
