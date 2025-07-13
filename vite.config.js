import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    server: {
        hmr: {
            port: 5173,
            host: "localhost",
        },
        host: "localhost", // Use localhost instead of 0.0.0.0
        port: 5173,
        strictPort: true,
        origin: "http://localhost:5173", // Explicitly set origin
        cors: true,
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.jsx"],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
});
