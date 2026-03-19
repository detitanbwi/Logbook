const fs = require('fs');

const path = 'frontend/src/lib/stores/__tests__/stores.test.ts';
let content = fs.readFileSync(path, 'utf8');
content = content.replace("await auth.login({ nip: '12345', password: 'password123' });", 
`try { 
    await auth.login({ nip: '12345', password: 'password123' }); 
} catch (e) { 
    console.error('LOGIN ERROR:', e); 
}`);
fs.writeFileSync(path, content, 'utf8');

