const fs = require('fs');
let content = fs.readFileSync('frontend/src/lib/stores/__tests__/stores.test.ts', 'utf8');

// Replace mock paths to use the same index file that we import from
content = content.replace(/vi\.mock\('\.\.\/\.\.\/api\/services\/authService'/g, "vi.mock('../../api'");
content = content.replace(/vi\.mock\('\.\.\/\.\.\/api\/services\/staffLogbookService'/g, "vi.mock('../../api'");

// But wait, if we mock the whole api, we overwrite `api.clearToken()`, etc.
// A safer way is to just mock the methods on the already imported `authService`.
