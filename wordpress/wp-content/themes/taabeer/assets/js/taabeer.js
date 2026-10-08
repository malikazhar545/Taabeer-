(function () {
  "use strict";

  document.documentElement.classList.remove("no-js");

  var header = document.querySelector("[data-site-header]");
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
    try { return window.localStorage.getItem(cookieName); } catch (error) { return null; }
  }

  function loadAnalytics() {
    var id = window.TaabeerTheme ? TaabeerTheme.gaId : "";
    if ( !id || document.querySelector("script[data-taabeer-analytics]") ) return;
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
    try { window.localStorage.setItem(cookieName, choice); } catch (error) {}
    document.cookie = cookieName + "=" + encodeURIComponent(choice) + "; Max-Age=15552000; Path=/; SameSite=Lax";
    if ( banner ) banner.hidden = true;
    if ( choice === "all" ) loadAnalytics();
  }

  if ( banner ) {
    var choice = getChoice();
    banner.hidden = Boolean(choice);
    banner.querySelectorAll("[data-cookie-choice]").forEach(function (button) {
      button.addEventListener("click", function () { saveChoice(button.getAttribute("data-cookie-choice")); });
    });
    settingsButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        banner.hidden = false;
        var first = banner.querySelector("button");
        if ( first ) first.focus();
      });
    });
    if ( choice === "all" ) loadAnalytics();
  }

  var formStatus = document.querySelector("[data-form-status]");
  if ( formStatus ) formStatus.focus();
}());
