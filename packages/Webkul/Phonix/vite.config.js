import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";

export default defineConfig(({ mode }) => {
    const envDir = "../../../";

    Object.assign(process.env, loadEnv(mode, envDir));

    return {
        build: {
            emptyOutDir: true,
            minify: "esbuild",
            cssCodeSplit: true,
        },

        envDir,

        server: {
            host: process.env.VITE_HOST || "localhost",
            port: process.env.VITE_PORT || 5174,
            cors: true,
        },

        plugins: [
            laravel({
                hotFile: "../../../public/shop-phonix-vite.hot",
                publicDirectory: "../../../public",
                buildDirectory: "themes/shop/phonix/build",
                input: [
                    "src/Resources/assets/css/app.css",
                    "src/Resources/assets/js/app.js",

                    "src/Resources/assets/images/default-language.svg",
                    "src/Resources/assets/images/favicon.ico",
                    "src/Resources/assets/images/large-product-placeholder.webp",
                    "src/Resources/assets/images/logo.svg",
                    "src/Resources/assets/images/medium-product-placeholder.webp",
                    "src/Resources/assets/images/small-product-placeholder.webp",
                    "src/Resources/assets/images/spinner.svg",
                    "src/Resources/assets/images/thank-you.png",
                ],
                refresh: true,
                preload: false,
            }),
        ],

        resolve: {
            alias: {
                "@phonix": path.resolve(__dirname, "src/Resources/assets"),
            },
        },

        experimental: {
            renderBuiltUrl(filename, { hostId, hostType, type }) {
                if (hostType === "css") {
                    return path.basename(filename);
                }
            },
        },
    };
});
