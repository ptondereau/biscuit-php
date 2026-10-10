import nodeResolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import copy from 'rollup-plugin-copy';
import { importMetaAssets } from '@web/rollup-plugin-import-meta-assets';

export default {
    input: 'src/index.js',
    output: {
        dir: 'dist',
        format: 'esm'
    },
    plugins: [
        nodeResolve({ browser: true }),
        commonjs({
            include: 'node_modules/**'
        }),
        copy({
            targets: [
                // All assets including WASM files go to dist/assets
                { src: 'node_modules/@biscuit-auth/web-components/dist/assets/*', dest: 'dist/assets' }
            ],
        }),
        importMetaAssets()
    ]
};
