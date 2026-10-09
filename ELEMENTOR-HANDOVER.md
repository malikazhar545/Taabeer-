# TAABEER 1.1.2 — editing and launch

This release uses Elementor Free. It does not need Elementor Pro and does not modify any official plugin files.

## Where to edit

Open **TAABEER → Setup & editing** in the main WordPress sidebar. Each page and reusable template has an **Edit with Elementor** link. Text, images, headings, links, spacing and colours are editable. Native Elementor controls are used wherever possible; TAABEER widgets provide functional navigation, contact forms and dynamic WordPress/WooCommerce content.

**TAABEER → Header & footer** contains the single site-wide header and footer. Change navigation links in the header's TAABEER Navigation widget. Change footer text and links in its native Elementor widgets. Do not edit the older recovery layouts named “TAABEER Header” and “TAABEER Footer”.

In **Contact → Edit with Elementor**, select the form and enter the real inbox under **Receive enquiries at**. A blank value uses the website contact email or WordPress administrator email. Labels, enquiry options, consent copy and response messages are editable. The address, phone and email on the left are separate Elementor Icon Box widgets. The phone and email are visibly marked placeholders; replace them before client handover. Test delivery through the hosting mail service; SMTP credentials may be needed if the host cannot deliver WordPress mail.

Use **Elementor → Site Settings** for global colours and typography. The editor and frontend load the same theme styling. Explicit Elementor settings override defaults. Routine text edits do not reset saved styles. Check desktop, tablet and mobile previews before publishing.

## Plugin foundation

On the first administrator request after installing this release, setup installs and activates missing official plugins from WordPress.org: Elementor, Ultimate Addons for Elementor / Header Footer Builder, Yoast SEO, WooCommerce, UpdraftPlus and Limit Login Attempts Reloaded. Existing plugin files are not edited. A second admin request may be required after activation to finish the layout migration. Installation errors appear in an admin notice; resolve the reported issue and reload.

The migration saves a recovery copy of each original Elementor page in `_taabeer_before_visual_1_1`, retains existing content, and skips already converted pages on later requests. Demo reimport reuses nested pages instead of creating duplicates. Shared layouts are stored in the database. Repository updates replace theme code; they do not synchronise every live database edit back to GitHub.

UpdraftPlus schedules daily file and database backups where no active schedule exists. Connect client-owned remote storage in UpdraftPlus and confirm successful backups. Hosting daily backups and offsite restore testing need hosting/storage access; plugin installation alone does not verify them. Login protection uses the official plugin's settings.

## SEO and content

Page titles, descriptions and initial focus phrases are prepared in Yoast without replacing custom client metadata. Edit them in the page's Yoast panel; edit slugs in WordPress page settings. Yoast provides the sitemap at `/sitemap_index.xml`. A focus phrase is an editorial aid, not a ranking guarantee. No numeric SEO score is fabricated. Unapproved content retains its review status and noindex protection; confirm final copy, imagery and legal wording before indexing.

Analytics measurement ID is under TAABEER setup and only loads after analytics consent. Search Console verification can be entered in Yoast once the client supplies access. Newsletter signup remains unavailable until an email platform and privacy wording are approved.

## Commerce remains in draft

WooCommerce is active, but purchasing is disabled. Shop, basket, checkout and account pages remain draft; sample products are draft and not purchasable. Categories, Size/Colour/Material attributes and inactive UK/international delivery zones are prepared. No shipping rates, tax rules or payment accounts are invented.

The product, shop and editorial archive templates are editable from TAABEER setup. Dynamic fields use the current product/story/collection; product prices, stock, variations, shipping and orders are managed in WooCommerce, not Elementor. Basket/checkout/account business controls remain official WooCommerce output inside editable Elementor page layouts.

Before enabling purchases: replace all sample data; supply approved product imagery and copy; add price, SKU, inventory and variations; configure payment, delivery rates, taxes and returns; test successful and failed payments, order emails, mobile checkout and refunds. Then enable purchasing in TAABEER setup. Only prepared commerce pages are published by that control.

## Updates

Use **TAABEER → GitHub updates** to check and install a repository release. The existing updater remains compatible with its older Tools URL. Removing the updater stops repository checks; installed content and Elementor editing remain available.

Official plugin updates retain database content and custom theme code. No code can guarantee compatibility with every future major release: take a backup, test important upgrades on staging, and check editor, forms and checkout afterwards. Keep the Taabeer theme active to retain its custom Elementor widgets.

## 1.1.1 repairs and editorial pages

The theme repairs a missing or invalid Elementor active-kit reference through Elementor's official kit API. It reuses an existing published kit when available and creates one only when necessary. Newly created kits receive the Taabeer palette; existing kit settings are preserved.

Contemporary Pakistan, Pakistan in the Making, New Voices, and five regional notebooks use native Elementor containers, headings, text, images and buttons. Their one-time redesign stores the previous content, Elementor data and status under `_taabeer_before_editorial_1_1_1`; later admin visits do not reapply the migration. The imagery is identified as material studies, not documentary photographs of the regions. These are introductory editorial pages; verified maker profiles remain future content.

The older `tools.php?page=taabeer-updates` link has an explicit screen title so PHP 8.3 does not receive a null admin title after the menu relocation. Official WordPress/plugin files remain unchanged. The homepage partnership button has a dark background and white label.

The kit, content preservation, draft commerce and legacy updater title were checked locally. Live editor checks, responsive visual review of the new layouts, and actual Yoast analysis scores still require a connected browser; setting metadata alone does not prove a green analysis score.

## 1.1.2 consistent green accents

The five regional notebooks and three Discover subpages use a green introduction panel beside the lead image. The regional overview uses matching green card captions, and About has a smaller green connection panel. Colours are stored in native Elementor container Background, Heading Text Colour, Text Editor Text Colour and Button controls. The shared stylesheet supplies responsive spacing.

The one-time styling migration preserves existing paragraphs, image references, links and element IDs. It keeps a recovery copy in `_taabeer_before_green_1_1_2` and does not reapply on later admin visits. Existing explicit style values are retained where present. All ten affected routes were checked locally for successful rendering, one H1, native Elementor green/ivory CSS and PHP warnings. The intended ivory-on-green combination has a 10.54:1 contrast ratio. Browser-based visual verification and live deployment remain pending while browser control is unavailable.
