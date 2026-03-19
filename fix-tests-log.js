const fs = require('fs');

const path = 'frontend/src/lib/stores/__tests__/stores.test.ts';

let content = fs.readFileSync(path, 'utf8');
content = content.replace("await auth.login({ nip: '12345', password: 'password123' });",
`const res = await auth.login({ nip: '12345', password: 'password123' });
console.log('RES:', res);
console.log('TOKEN:', auth.token.current);`);
fs.writeFileSync(path, content, 'utf8');

