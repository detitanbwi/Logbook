const fs = require('fs');

const path = 'frontend/src/lib/stores/__tests__/stores.test.ts';
let content = fs.readFileSync(path, 'utf8');
content = content.replace(/from '\.\.\/\.\.\/api'/g, "from '$lib/api'");
content = content.replace(/import \{ AuthStore \} from '\.\.\/auth\.svelte';/, "import { AuthStore } from '$lib/stores/auth.svelte';");
content = content.replace(/import \{ LogbookStore \} from '\.\.\/logbook\.svelte';/, "import { LogbookStore } from '$lib/stores/logbook.svelte';");
fs.writeFileSync(path, content, 'utf8');

