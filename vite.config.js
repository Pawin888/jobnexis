import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],

            // ❌ ปิด refresh อัตโนมัติของ Blade เพื่อลดการ full reload
            refresh: [
                'resources/js/**',
                'resources/css/**',
                // ไม่ใส่ resources/views/** เพื่อไม่ให้ reload เมื่อแก้ Blade
            ],
        }),
    ],
});
