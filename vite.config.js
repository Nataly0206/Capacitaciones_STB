import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Sin esto, el minificador reescribe @media (min-width: X) como
        // @media (width>=X), que navegadores anteriores a Chrome/Edge 104 y
        // Safari 16.4 ignoran por completo (se pierden grids y layouts responsivos).
        cssTarget: ['chrome87', 'edge88', 'firefox78', 'safari14'],
    },
});
