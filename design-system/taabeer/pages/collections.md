# Collections overview

The client's approved palette overrides the generated master palette: deep green
`#173f35`, darker green `#0d2c25`, ivory `#f7f3ea`, warm ivory `#eee8dc`, gold
`#b59a60`. Retain the theme's local serif and sans-serif font stacks.

Use the UI/UX Pro Editorial Grid / Magazine direction: photography, print-like
hierarchy, fine rules, sharp edges, and generous spacing. No shadows or rounded cards.

- Desktop: compact split introduction; three image cards followed by two wider cards.
- Tablet: two columns with the last card spanning both columns.
- Mobile: one column, readable descriptions, full-card link targets and 20px side gutters.
- Footer: reuse the reviewed homepage composition on this page with 96px desktop / 64px mobile clearance.
- Interaction: visible keyboard focus, restrained image hover, reduced-motion support.

Implementation: `assets/css/collections.css` loads only on the Collections overview.
The 1.0.4 migration converts recognised legacy heading/description pairs into editable
HTML cards. It preserves existing text, formatting, URLs, widget IDs, and other Elementor
settings. Unrecognised list layouts are left unchanged. A recovery copy is stored in
`_taabeer_collections_before_1_0_4`; repeated updates do not convert cards again.
Images can be replaced in the existing Elementor Text Editor widget.

Validation: desktop 1440px, tablet 820px, mobile 390px; card destinations; PHP syntax;
custom content preservation; idempotence; unfamiliar-layout preservation; theme ZIP validation.
