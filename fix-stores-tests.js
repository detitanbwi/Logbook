const fs = require('fs');
let content = fs.readFileSync('frontend/src/lib/stores/__tests__/stores.test.ts', 'utf8');

if (!content.includes("vi.mock('$env/dynamic/public'")) {
    content = "import { vi } from 'vitest';\nvi.mock('$env/dynamic/public', () => ({ env: { PUBLIC_API_URL: 'http://localhost:8000/api' } }));\n" + content;
    fs.writeFileSync('frontend/src/lib/stores/__tests__/stores.test.ts', content, 'utf8');
}
