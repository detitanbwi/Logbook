const fs = require('fs');

let schemaTest = fs.readFileSync('frontend/src/lib/api/__tests__/schema.test.ts', 'utf8');
schemaTest = schemaTest.replace(/import\s*\{\s*LoginRequestSchema,\s*LoginResponseSchema,\s*ChangePasswordRequestSchema,\s*StartLogbookRequestSchema,\s*ToggleKpiRequestSchema,\s*SubmitLogbookRequestSchema,\s*RateLogbookRequestSchema\s*\}\s*from\s*'\.\.\/schemas\/auth\.schema';/, 
`import {
	LoginRequestSchema,
	LoginResponseSchema,
	ChangePasswordRequestSchema
} from '../schemas/auth.schema';`);
fs.writeFileSync('frontend/src/lib/api/__tests__/schema.test.ts', schemaTest, 'utf8');


let storeTest = fs.readFileSync('frontend/src/lib/stores/__tests__/stores.test.ts', 'utf8');
storeTest = storeTest.replace(/constructor\(key, initialValue\)/g, "constructor(key: string, initialValue: any)");
fs.writeFileSync('frontend/src/lib/stores/__tests__/stores.test.ts', storeTest, 'utf8');

