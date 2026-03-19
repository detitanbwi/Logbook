const fs = require('fs');

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

replaceInFile('frontend/src/lib/api/services/authService.ts', [
    [/from '\.\/client'/g, "from '../core/client'"],
    [/from '\.\/schema'/g, "from '../schemas/auth.schema'"]
]);

replaceInFile('frontend/src/lib/api/services/staffLogbookService.ts', [
    [/from '\.\/client'/g, "from '../core/client'"],
    [/from '\.\/schema'/g, "from '../schemas/logbook.schema'"]
]);

replaceInFile('frontend/src/lib/api/services/managerLogbookService.ts', [
    [/from '\.\/client'/g, "from '../core/client'"],
    [/from '\.\/schema'/g, "from '../schemas/logbook.schema'"]
]);

// Let's also check api.test.ts again to make sure everything is right
replaceInFile('frontend/src/lib/api/__tests__/api.test.ts', [
    [/import \{ authService \} from '\.\.\/authService';/g, "import { authService } from '../services/authService';"],
    [/import \{ staffLogbookService \} from '\.\.\/staffLogbookService';/g, "import { staffLogbookService } from '../services/staffLogbookService';"],
    [/import \{ api \} from '\.\.\/client';/g, "import { api } from '../core/client';"]
]);

