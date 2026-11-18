const { chromium } = require('playwright'); const fs = require('fs'); const path = require('path');
const themeDir = path.resolve(process.argv[2]); const slug = path.basename(themeDir);
const html = fs.readFileSync(path.join(__dirname, 'pages', slug, 'home.html'), 'utf8');
const mime = { '.css': 'text/css', '.js': 'application/javascript' };
(async () => { const b = await chromium.launch(); let fail = 0; const ok = (c, m) => { if (!c) { fail++; console.log('FAIL', m); } };
  for (const width of [320, 375, 600, 700, 1024]) {
    const ctx = await b.newContext({ viewport: { width, height: 800 }, reducedMotion: 'reduce' }); const p = await ctx.newPage();
    await p.route('https://example.test/**', r => { const u = new URL(r.request().url()); if (u.pathname === '/page') return r.fulfill({ contentType: 'text/html', body: html });
      const m = u.pathname.match(/^\/themes\/[^/]+\/(.+)$/); if (m) { const f = path.join(themeDir, m[1]); if (fs.existsSync(f)) return r.fulfill({ contentType: mime[path.extname(f)] || 'text/plain', body: fs.readFileSync(f) }); } return r.fulfill({ status: 204, body: '' }); });
    await p.goto('https://example.test/page'); await p.waitForTimeout(200);
    const vis = async sel => p.evaluate(s => [...document.querySelectorAll(s)].filter(e => { const r = e.getBoundingClientRect(); const cs = getComputedStyle(e); return r.width > 0 && r.height > 0 && cs.visibility !== 'hidden' && cs.display !== 'none' && r.right <= innerWidth + 0.5 && r.left >= -0.5; }).length, sel);
    const toggleVisible = await vis('#mobileMenuToggle');
    const headerAuth = await vis('.ac-header-auth'); const navAuthClosed = await vis('.ac-nav-auth');
    if (width <= 768) { ok(toggleVisible === 1, `${width}px: Menüknopf sichtbar im Bild`); }
    if (width <= 640) { ok(headerAuth === 0, `${width}px: Header-Buttons ausgeblendet`); }
    if (width > 640) { ok(headerAuth === 2, `${width}px: Header-Buttons sichtbar (${headerAuth})`); }
    ok(navAuthClosed === 0, `${width}px: Panel-Aktionen bei geschlossenem Menü nicht sichtbar (${navAuthClosed})`);
    ok(await p.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${width}px: kein horizontales Scrollen`);
    if (width <= 768) {
      await p.click('#mobileMenuToggle'); await p.waitForTimeout(100);
      ok(await p.getAttribute('#mobileMenuToggle', 'aria-expanded') === 'true', `${width}px: aria-expanded true`);
      if (width <= 640) { ok(await vis('.ac-nav-auth .ac-btn') === 2, `${width}px: Konto-Aktionen im offenen Panel`); }
      if (width === 375) await p.screenshot({ path: path.join(__dirname, 'menu-open.png'), clip: { x: 0, y: 0, width, height: 420 } });
      await p.keyboard.press('Escape');
      ok(await p.getAttribute('#mobileMenuToggle', 'aria-expanded') === 'false', `${width}px: Escape schließt`);
    }
    await ctx.close(); }
  await b.close(); console.log(fail === 0 ? 'Menü-Checks ok' : fail + ' Fehler'); })();
