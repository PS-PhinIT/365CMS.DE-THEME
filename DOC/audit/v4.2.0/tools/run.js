// node run.js <themeDir>  – prüft pages/<slug>/*.html in Chromium (axe-core + eigene WCAG-2.2-Prüfungen)
const { chromium } = require('playwright');
const fs = require('fs'); const path = require('path');
const themeDir = path.resolve(process.argv[2]); const slug = path.basename(themeDir);
const pagesDir = path.join(__dirname, 'pages', slug);
const axeSrc = fs.readFileSync(path.join(__dirname, 'node_modules/axe-core/axe.min.js'), 'utf8');
const only = process.argv[3] ? process.argv[3].split(',') : null;
const viewports = [{ name: 'desktop', width: 1280, height: 900 }, { name: 'mobil', width: 375, height: 800 }];
const mime = { '.css': 'text/css', '.js': 'application/javascript', '.png': 'image/png', '.svg': 'image/svg+xml', '.json': 'application/json' };

(async () => {
  const browser = await chromium.launch();
  const report = [];
  for (const file of fs.readdirSync(pagesDir).filter(f => f.endsWith('.html')).sort()) {
    const name = file.replace('.html', ''); if (only && !only.includes(name)) continue;
    const html = fs.readFileSync(path.join(pagesDir, file), 'utf8');
    for (const vp of viewports) {
      for (const motion of ['no-preference', 'reduce']) {
        if (motion === 'no-preference' && name !== 'home' && name !== 'index') continue;
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height }, reducedMotion: motion });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', e => errors.push('pageerror: ' + e.message));
        await page.route('https://example.test/**', route => {
          const url = new URL(route.request().url());
          if (url.pathname === '/page') return route.fulfill({ status: 200, contentType: 'text/html', body: html });
          const m = url.pathname.match(/^\/themes\/[^/]+\/(.+)$/);
          if (m) { const f = path.join(themeDir, m[1]); if (fs.existsSync(f)) return route.fulfill({ status: 200, contentType: mime[path.extname(f)] || 'text/plain', body: fs.readFileSync(f) }); }
          if (url.pathname.startsWith('/assets/css/')) { const f = (process.env.CMS_CORE_DIR || path.resolve(__dirname, '../../../../../365cms.de/CMS')) + url.pathname; if (fs.existsSync(f)) return route.fulfill({ status: 200, contentType: 'text/css', body: fs.readFileSync(f) }); }
          if (url.pathname.startsWith('/uploads/')) return route.fulfill({ status: 200, contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="300"><rect width="600" height="300" fill="#ccd"/></svg>' });
          return route.fulfill({ status: 204, body: '' });
        });
        await page.goto('https://example.test/page', { waitUntil: 'load' });
        if (name === 'contact') { await page.addStyleTag({ url: 'https://example.test/assets/css/forms-public.css' }); }
        await page.waitForTimeout(motion === 'no-preference' ? 1500 : 150);
        await page.addStyleTag({ content: 'html{scroll-behavior:auto!important}' });
        await page.addScriptTag({ content: axeSrc });
        const axe = await page.evaluate(async () => {
          const r = await axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice'] }, resultTypes: ['violations'] });
          return r.violations.map(v => ({ id: v.id, impact: v.impact, help: v.help, nodes: v.nodes.slice(0, 4).map(n => n.target.join(' ') + ' → ' + (n.failureSummary || '').split('\n').slice(1, 3).join(' | ').slice(0, 220)), count: v.nodes.length }));
        });
        const extra = await page.evaluate(() => {
          const out = {};
          out.overflowX = document.documentElement.scrollWidth - window.innerWidth;
          const small = [];
          document.querySelectorAll('a[href], button, input:not([type=hidden]), select, textarea, summary, [role=button]').forEach(el => {
            const r = el.getBoundingClientRect(); const cs = getComputedStyle(el);
            if (r.width === 0 || r.height === 0 || cs.visibility === 'hidden' || cs.display === 'none') return;
            if (el.closest('[hidden], [inert]')) return;
            const inline = el.tagName === 'A' && getComputedStyle(el).display === 'inline' && el.closest('p, li, dd, figcaption, td');
            if (!inline && (r.width < 24 || r.height < 24)) small.push((el.id ? '#' + el.id : el.tagName.toLowerCase() + (el.className ? '.' + String(el.className).split(' ')[0] : '') + (el.getAttribute('href') ? '[' + el.getAttribute('href').slice(0, 24) + ']' : '')) + ' „' + (el.textContent || '').trim().slice(0, 20) + '“ ' + Math.round(r.width) + '×' + Math.round(r.height));
          });
          out.small = [...new Set(small)].slice(0, 12);
          return out;
        });
        // Fokus nicht verdeckt (2.4.11): Tab-Durchlauf, jedes fokussierte Element muss im sichtbaren, nicht überdeckten Bereich liegen
        const hidden = [];
        for (let i = 0; i < 40; i++) {
          await page.keyboard.press('Tab'); await page.waitForTimeout(30);
          const r = await page.evaluate(() => {
            const el = document.activeElement; if (!el || el === document.body) return null;
            const b = el.getBoundingClientRect(); if (b.width === 0 || b.height === 0) return { label: 'unsichtbar fokussiert: ' + el.tagName + '.' + el.className };
            const cx = Math.min(Math.max(b.left + b.width / 2, 0), innerWidth - 1), cy = Math.min(Math.max(b.top + b.height / 2, 0), innerHeight - 1);
            const top = document.elementFromPoint(cx, cy);
            const covered = top && top !== el && !el.contains(top) && !top.contains(el);
            const offscreen = b.bottom < 0 || b.top > innerHeight;
            return (covered || offscreen) ? { label: el.tagName.toLowerCase() + '.' + String(el.className).split(' ')[0] + (el.textContent ? ' „' + el.textContent.trim().slice(0, 24) + '“' : ''), by: covered ? top.tagName + '.' + String(top.className).split(' ')[0] : 'außerhalb' } : null;
          });
          if (r) hidden.push(JSON.stringify(r));
        }
        report.push({ name, vp: vp.name, motion, axe, overflowX: extra.overflowX, small: extra.small, hiddenFocus: [...new Set(hidden)].slice(0, 6), errors });
        await ctx.close();
      }
    }
  }
  await browser.close();
  const uniq = new Map(); let bad = 0;
  for (const r of report) {
    const tag = `${r.name}/${r.vp}${r.motion === 'reduce' ? '' : '/motion'}`;
    const add = (k, detail) => { const e = uniq.get(k) || { detail, pages: new Set() }; e.pages.add(tag); uniq.set(k, e); };
    r.axe.forEach(v => add(`axe ${v.id} (${v.impact})`, v.nodes.slice(0, 3).join('\n        ')));
    if (r.overflowX > 1) add('horizontaler Überlauf', r.overflowX + 'px');
    // Zielgrößen prüft axe (target-size) inkl. Abstandsausnahme von WCAG 2.5.8
    if (r.hiddenFocus.length) add('Fokus verdeckt', r.hiddenFocus.join('; '));
    r.errors.forEach(e => add('JS-Fehler', e));
    if (r.axe.length || r.overflowX > 1 || r.hiddenFocus.length || r.errors.length) bad++;
  }
  for (const [k, e] of uniq) console.log(`✗ ${k}  [${[...e.pages].slice(0, 6).join(', ')}${e.pages.size > 6 ? ' … +' + (e.pages.size - 6) : ''}]\n        ${e.detail}`);
  console.log(`\n${report.length} Läufe, ${bad} mit Befunden`);
})();
