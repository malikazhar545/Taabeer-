(function () {
  "use strict";

  document.documentElement.classList.remove("no-js");

  var header = document.querySelector("[data-site-header], header.site-header");
  var toggle = document.querySelector("[data-menu-toggle]");
  var navigation = document.querySelector("[data-primary-nav]");

  if ( header && toggle && navigation ) {
    toggle.addEventListener("click", function () {
      var open = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", String(!open));
      navigation.classList.toggle("is-open", !open);
      header.classList.toggle("menu-active", !open);
      document.body.classList.toggle("menu-open", !open);
      var label = toggle.querySelector(".screen-reader-text");
      if ( label && window.TaabeerTheme ) {
        label.textContent = open ? TaabeerTheme.menuOpen : TaabeerTheme.menuClose;
      }
    });

    navigation.addEventListener("click", function (event) {
      if ( event.target.closest("a") && navigation.classList.contains("is-open") ) {
        toggle.click();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && toggle.getAttribute("aria-expanded") === "true") {
        toggle.click();
        toggle.focus();
      }
    });

    window.addEventListener("scroll", function () {
      header.classList.toggle("is-sticky", window.scrollY > 90);
    }, { passive: true });
  }

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var reveals = document.querySelectorAll(".reveal-on-scroll");
  if ( reduceMotion || !("IntersectionObserver" in window) ) {
    reveals.forEach(function (element) { element.classList.add("is-visible"); });
  } else {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if ( entry.isIntersecting ) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    }, { rootMargin: "0px 0px -8%", threshold: 0.08 });
    reveals.forEach(function (element) { observer.observe(element); });
  }

  var banner = document.querySelector("[data-cookie-banner]");
  var settingsButtons = document.querySelectorAll("[data-cookie-settings]");
  var cookieName = window.TaabeerTheme ? TaabeerTheme.cookieName : "taabeer_cookie_choice";

  function getChoice() {
    try {
      var saved = JSON.parse(window.localStorage.getItem(cookieName) || 'null');
      if (saved && saved.expires > Date.now() && (saved.choice === 'all' || saved.choice === 'essential')) return saved.choice;
      if (saved) window.localStorage.removeItem(cookieName);
    } catch (error) {}
    var stored = document.cookie.split(';').map(function(item){return item.trim();}).find(function(item){return item.indexOf(cookieName + '=') === 0;});
    if (stored) { var value = stored.slice(cookieName.length + 1); if (value === 'all' || value === 'essential') return value; }
    return null;
  }

  function loadAnalytics() {
    var id = window.TaabeerTheme ? TaabeerTheme.gaId : "";
    if ( !id ) return;
    window['ga-disable-' + id] = false;
    if (window.gtag) window.gtag('consent', 'update', {analytics_storage: 'granted'});
    if (document.querySelector("script[data-taabeer-analytics]")) return;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag("js", new Date());
    window.gtag("config", id, { anonymize_ip: true });
    var script = document.createElement("script");
    script.async = true;
    script.src = "https://www.googletagmanager.com/gtag/js?id=" + encodeURIComponent(id);
    script.setAttribute("data-taabeer-analytics", "true");
    document.head.appendChild(script);
  }

  function saveChoice(choice) {
    try { window.localStorage.setItem(cookieName, JSON.stringify({choice: choice, expires: Date.now() + 15552000000})); } catch (error) {}
    document.cookie = cookieName + "=" + encodeURIComponent(choice) + "; Max-Age=15552000; Path=/; SameSite=Lax";
    if ( banner ) banner.hidden = true;
    document.body.classList.remove("cookie-banner-open");
    if ( choice === "all" ) loadAnalytics();
    else {
      var id = window.TaabeerTheme ? TaabeerTheme.gaId : '';
      if (id) window['ga-disable-' + id] = true;
      if (window.gtag) window.gtag('consent', 'update', {analytics_storage: 'denied'});
      document.cookie.split(';').forEach(function (item) {
        var name = item.split('=')[0].trim();
        if (!/^_ga($|_)/.test(name)) return;
        var expiry = name + '=; Max-Age=0; Path=/; SameSite=Lax';
        document.cookie = expiry;
        var parts = window.location.hostname.split('.');
        while(parts.length >= 2) { document.cookie = expiry + '; Domain=.' + parts.join('.'); parts.shift(); }
      });
    }
  }

  if ( banner ) {
    var choice = getChoice();
    banner.hidden = Boolean(choice);
    document.body.classList.toggle("cookie-banner-open", !choice);
    banner.querySelectorAll("[data-cookie-choice]").forEach(function (button) {
      button.addEventListener("click", function () { saveChoice(button.getAttribute("data-cookie-choice")); });
    });
    settingsButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        banner.hidden = false;
        document.body.classList.add("cookie-banner-open");
        var first = banner.querySelector("button");
        if ( first ) first.focus();
      });
    });
    if ( choice === "all" ) loadAnalytics();
  }

  var formStatus = document.querySelector("[data-form-status]");
  if ( formStatus ) formStatus.focus();
}());
