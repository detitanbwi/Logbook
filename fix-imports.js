const fs = require('fs');
const path = require('path');

function replaceInFile(filePath, replacements) {
    if (!fs.existsSync(filePath)) return;
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;
    for (const [regex, replacement] of replacements) {
        content = content.replace(regex, replacement);
    }
    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Updated ${filePath}`);
    }
}

// 1. Fix schema.test.ts
replaceInFile('frontend/src/lib/api/__tests__/schema.test.ts', [
    [/from '\.\.\/schemas\/logbook\.schema'/g, "from '../index'"]
]);

// 2. Fix api.test.ts
replaceInFile('frontend/src/lib/api/__tests__/api.test.ts', [
    [/from '\.\.\/services\/authService'/g, "from '../index'"],
    [/from '\.\.\/services\/staffLogbookService'/g, "from '../index'"],
    [/from '\.\.\/core\/client'/g, "from '../index'"],
    [/import \{ authService \} from '\.\.\/index';\nimport \{ staffLogbookService \} from '\.\.\/index';\nimport \{ api \} from '\.\.\/index';/g, "import { authService, staffLogbookService, api } from '../index';"]
]);

// 3. Fix stores/auth.svelte.ts
replaceInFile('frontend/src/lib/stores/auth.svelte.ts', [
    [/from '\$lib\/api\/services\/authService'/g, "from '$lib/api'"]
]);

// 4. Fix stores/logbook.svelte.ts
replaceInFile('frontend/src/lib/stores/logbook.svelte.ts', [
    [/from '\$lib\/api\/services\/staffLogbookService'/g, "from '$lib/api'"]
]);

// 5. Fix stores/__tests__/stores.test.ts
replaceInFile('frontend/src/lib/stores/__tests__/stores.test.ts', [
    [/from '\.\.\/\.\.\/api\/services\/authService'/g, "from '../../api'"],
    [/from '\.\.\/\.\.\/api\/services\/staffLogbookService'/g, "from '../../api'"],
    [/import \{ authService \} from '\.\.\/\.\.\/api';\nimport \{ staffLogbookService \} from '\.\.\/\.\.\/api';\nimport \{ api \} from '\.\.\/\.\.\/api';/g, "import { authService, staffLogbookService, api } from '../../api';"]
]);

