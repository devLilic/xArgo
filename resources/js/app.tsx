import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME ?? 'xArgo';
const pages = import.meta.glob('./Pages/**/*.{jsx,tsx}');

createInertiaApp({
    title: (title) => (title ? `${title} | ${appName}` : appName),
    resolve: async (name) => {
        const page = pages[`./Pages/${name}.tsx`] ?? pages[`./Pages/${name}.jsx`];

        if (! page) {
            throw new Error(`Page not found: ${name}`);
        }

        const module = await page();

        return 'default' in module ? module.default : module;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#0f766e',
    },
});
