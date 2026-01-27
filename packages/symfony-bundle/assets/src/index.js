import { initialize } from '@biscuit-auth/web-components';

async function setup() {
    await initialize();
    console.log('Biscuit web components initialized');
}

setup();
