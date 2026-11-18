const { chromium } = require('playwright'); const fs = require('fs'); const path = require('path');
const [themeDir, name, width, out, clip] = [path.resolve(process.argv[2]), process.argv[3], +process.argv[4], process.argv[5], +(process.argv[6] || 0)];
const slug = path.basename(themeDir); const html = fs.readFileSync(path.join(__dirname, 'pages', slug, name + '.html'), 'utf8');
const mime = { '.css': 'text/css', '.js': 'application/javascript', '.svg': 'image/svg+xml' };
(async () => { const b = await chromium.launch(); const ctx = await b.newContext({ viewport: { width, height: 800 }, reducedMotion: 'reduce' }); const p = await ctx.newPage();
  await p.route('https://example.test/**', r => { const u = new URL(r.request().url()); if (u.pathname === '/page') return r.fulfill({ contentType: 'text/html', body: html });
    const m = u.pathname.match(/^\/themes\/[^/]+\/(.+)$/); if (m) { const f = path.join(themeDir, m[1]); if (fs.existsSync(f)) return r.fulfill({ contentType: mime[path.extname(f)] || 'text/plain', body: fs.readFileSync(f) }); } return r.fulfill({ status: 204, body: '' }); });
  await p.goto('https://example.test/page'); await p.waitForTimeout(300);
  await p.screenshot({ path: out, clip: clip ? { x: 0, y: 0, width, height: clip } : undefined, fullPage: !clip }); await b.close(); })();
