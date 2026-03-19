const fs = require('fs');

const path = 'frontend/src/lib/stores/__tests__/stores.test.ts';
let content = fs.readFileSync(path, 'utf8');

const mockRuned = `
vi.mock('runed', () => {
    return {
        PersistedState: class {
            current = null;
            constructor(key, initialValue) {
                this.current = initialValue;
            }
        }
    };
});
`;

content = content.replace("vi.mock('$env/dynamic/public'", mockRuned + "vi.mock('$env/dynamic/public'");
fs.writeFileSync(path, content, 'utf8');
