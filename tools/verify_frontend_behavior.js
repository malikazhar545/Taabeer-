// Logic-only regression tests. No browser automation or network requests.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('wordpress/wp-content/themes/taabeer/assets/js/taabeer.js', 'utf8');
function element(attrs = {}) {
  const classes = new Set();
  return {
    attrs, events: {}, hidden: false, focused: false,
    classList: {add: c => classes.add(c), remove: c => classes.delete(c), contains: c => classes.has(c), toggle(c, on) { if (on) classes.add(c); else classes.delete(c); }},
    addEventListener(name, fn) { this.events[name] = fn; },
    getAttribute(name) { return this.attrs[name]; },
    setAttribute(name, value) { this.attrs[name] = value; },
    querySelector() { return null; }, querySelectorAll() { return []; },
    click() { this.events.click?.({}); }, focus() { this.focused = true; }
  };
}
function setup(stored) {
  const header = element(), menu = element(), toggle = element({'aria-expanded': 'false'});
  const banner = element(), all = element({'data-cookie-choice': 'all'}), essential = element({'data-cookie-choice': 'essential'}), settings = element();
  banner.querySelectorAll = () => [all, essential]; banner.querySelector = () => all;
  const cookies = new Map(), storage = new Map(), scripts = [], events = {};
  if (stored) storage.set('taabeer_cookie_choice', JSON.stringify(stored));
  const document = {
    documentElement: element(), body: element(),
    head: {appendChild(script) { scripts.push(script); }},
    createElement() { return element(); },
    querySelector(selector) {
      if (selector === '[data-site-header], header.site-header') return header;
      if (selector === '[data-menu-toggle]') return toggle;
      if (selector === '[data-primary-nav]') return menu;
      if (selector === '[data-cookie-banner]') return banner;
      if (selector === 'script[data-taabeer-analytics]') return scripts[0] || null;
      return null;
    },
    querySelectorAll(selector) { return selector === '[data-cookie-settings]' ? [settings] : []; },
    addEventListener(name, fn) { events[name] = fn; },
    get cookie() { return [...cookies].map(([k,v]) => `${k}=${v}`).join('; '); },
    set cookie(value) { const [name, val] = value.split(';')[0].split('='); if (value.includes('Max-Age=0')) cookies.delete(name); else cookies.set(name, val); }
  };
  const window = {
    TaabeerTheme: {gaId: 'G-LOCALTEST', cookieName: 'taabeer_cookie_choice'},
    location: {hostname: 'example.test'}, matchMedia: () => ({matches: true}), addEventListener() {},
    localStorage: {getItem: k => storage.get(k) || null, setItem: (k,v) => storage.set(k,v), removeItem: k => storage.delete(k)}
  };
  vm.runInNewContext(source, {document, window, TaabeerTheme: window.TaabeerTheme, Date, encodeURIComponent});
  return {header, menu, toggle, banner, all, essential, settings, cookies, storage, scripts, events, window};
}
const s = setup();
assert.equal(s.scripts.length, 0, 'analytics must not load before consent');
s.toggle.click(); assert.equal(s.toggle.attrs['aria-expanded'], 'true'); assert(s.menu.classList.contains('is-open'));
s.events.keydown({key: 'Escape'}); assert.equal(s.toggle.attrs['aria-expanded'], 'false'); assert(s.toggle.focused);
s.toggle.click(); s.menu.events.click({target: {closest: () => true}}); assert.equal(s.toggle.attrs['aria-expanded'], 'false');
s.all.click(); assert.equal(s.scripts.length, 1); assert.equal(s.window['ga-disable-G-LOCALTEST'], false);
s.cookies.set('_ga', 'test'); s.cookies.set('_ga_LOCALTEST', 'test'); s.essential.click();
assert.equal(s.window['ga-disable-G-LOCALTEST'], true); assert(!s.cookies.has('_ga')); assert(!s.cookies.has('_ga_LOCALTEST'));
s.all.click(); assert.equal(s.scripts.length, 1, 'reaccepting must not duplicate analytics'); assert.equal(s.window['ga-disable-G-LOCALTEST'], false);
s.settings.click(); assert.equal(s.banner.hidden, false); assert(s.all.focused);
const expired = setup({choice: 'all', expires: Date.now() - 1000}); assert.equal(expired.scripts.length, 0); assert.equal(expired.banner.hidden, false);
const remembered = setup({choice: 'essential', expires: Date.now() + 60000}); assert.equal(remembered.scripts.length, 0); assert.equal(remembered.banner.hidden, true);
console.log('PASS native Elementor menu opens, closes and restores keyboard focus');
console.log('PASS analytics consent, withdrawal, reacceptance and expiry; no network requests');
