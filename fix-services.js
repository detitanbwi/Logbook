const fs = require('fs');
const path = require('path');

const dir = 'frontend/src/lib/api/services';
const files = fs.readdirSync(dir);

for (const file of files) {
    if (file.endsWith('.ts')) {
        const filePath = path.join(dir, file);
        let content = fs.readFileSync(filePath, 'utf8');
        let original = content;
        
        // fix imports in services to point to ../core/client, ../core/types
        content = content.replace(/from '\.\.\/client'/g, "from '../core/client'");
        content = content.replace(/from '\.\.\/types'/g, "from '../core/types'");
        
        if (content !== original) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`Updated ${filePath}`);
        }
    }
}
