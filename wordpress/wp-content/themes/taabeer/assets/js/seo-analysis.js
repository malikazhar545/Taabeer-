(function () {
  'use strict';
  const settings = window.TaabeerSeoAnalysis;
  const panel = document.getElementById('taabeer-seo-analysis');
  if (!settings || !panel) return;
  const status = panel.querySelector('[data-analysis-status]');
  const button = panel.querySelector('[data-analysis-refresh]');
  const analysis = window.yoast && window.yoast.analysis;
  const Researcher = window.yoast && window.yoast.Researcher && window.yoast.Researcher.default;
  if (!analysis || !analysis.AnalysisWebWorker || !Researcher) {
    status.textContent = 'Yoast analysis could not load. Open a page in the Yoast editor to recalculate it.';
    button.disabled = true; return;
  }
  async function request(id, values) {
    const body = new URLSearchParams(Object.assign({action: 'taabeer_seo_analysis', nonce: settings.nonce, id}, values));
    const response = await fetch(settings.url, {method: 'POST', credentials: 'same-origin', body});
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.data && result.data.message || 'Could not save analysis.');
    return result.data;
  }
  // Same 20px Arial DOM measurement used by Yoast's snippet analysis.
  const measure = document.createElement('div');
  measure.style.cssText = 'position:absolute;left:-9999em;top:0;height:0;overflow:hidden;font-family:arial,sans-serif;font-size:20px;font-weight:400;';
  document.body.appendChild(measure);
  async function run(ids) {
    button.disabled = true;
    let completed = 0, failed = 0;
    try {
      const worker = new analysis.AnalysisWebWorker({postMessage: function () {}}, new Researcher());
      for (const id of ids) {
        status.textContent = 'Calculating ' + (completed + failed + 1) + ' of ' + ids.length + ' published pages...';
        try {
          const row = await request(id, {operation: 'read'});
          worker.initialize(id, {locale: row.options.locale, useCornerstone: row.cornerstone, keywordAnalysisActive: true, contentAnalysisActive: true});
          measure.innerText = row.options.title;
          row.options.titleWidth = measure.offsetWidth;
          const results = await worker.analyze(id, {paper: new analysis.Paper(row.html, row.options)});
          const seo = results.seo[''].score, readability = results.readability.score;
          if (!Number.isFinite(seo) || !Number.isFinite(readability)) throw new Error('Invalid Yoast results.');
          await request(id, {operation: 'save', hash: row.hash, seo, readability});
          completed++;
        } catch (error) { failed++; console.warn('TAABEER Yoast analysis:', id, error.message); }
        await new Promise(resolve => setTimeout(resolve, 40));
      }
      status.textContent = completed + ' analyses saved.' + (failed ? ' ' + failed + ' could not be completed; try Recalculate analyses.' : ' Refresh the page to see the updated native Yoast indicators.');
      if (completed && !failed) {
        const refresh = document.createElement('a'); refresh.href = window.location.href; refresh.className = 'button'; refresh.textContent = 'Show updated scores';
        panel.querySelector('[data-analysis-result-link]')?.remove(); refresh.dataset.analysisResultLink = 'true'; panel.querySelector('p').appendChild(refresh);
      }
    } catch (error) { status.textContent = 'Analysis unavailable: ' + error.message; }
    finally { button.disabled = false; }
  }
  button.addEventListener('click', () => run(settings.all));
  if (settings.pending.length) run(settings.pending);
  else status.textContent = 'Saved analyses are up to date. Recalculate after changing content or SEO settings.';
}());
