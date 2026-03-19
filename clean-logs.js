const fs = require('fs');
const path = 'frontend/src/lib/stores/__tests__/stores.test.ts';
let content = fs.readFileSync(path, 'utf8');

content = content.replace(/const res = await auth\.login\(\{ nip: '12345', password: 'password123' \}\);\nconsole\.log\('RES:', res\);\nconsole\.log\('TOKEN:', auth\.token\.current\);/g, "await auth.login({ nip: '12345', password: 'password123' });");

fs.writeFileSync(path, content, 'utf8');
