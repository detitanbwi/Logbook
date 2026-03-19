const fs = require('fs');
let content = fs.readFileSync('frontend/src/lib/api/__tests__/schema.test.ts', 'utf8');
content = content.replace(/from '\.\.\/index'/g, "from '../schemas/auth.schema';\nimport {\n\tStartLogbookRequestSchema,\n\tToggleKpiRequestSchema,\n\tSubmitLogbookRequestSchema,\n\tRateLogbookRequestSchema\n} from '../schemas/logbook.schema'");
fs.writeFileSync('frontend/src/lib/api/__tests__/schema.test.ts', content, 'utf8');
