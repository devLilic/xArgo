import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import type { ComponentType } from 'react';

const appName = import.meta.env.VITE_APP_NAME ?? 'xArgo';
const pages = import.meta.glob('./Pages/**/*.{jsx,tsx}');

createInertiaApp({
    title: (title) => (title ? `${title} | ${appName}` : appName),
    resolve: async (name) => {
        const page = pages[`./Pages/${name}.tsx`] ?? pages[`./Pages/${name}.jsx`];

        if (! page) {
            throw new Error(`Page not found: ${name}`);
        }

        const pageModule = (await page()) as {
            default: ComponentType;
        };

        return pageModule.default;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#0f766e',
    },
});
