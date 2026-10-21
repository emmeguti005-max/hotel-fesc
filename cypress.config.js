const { defineConfig } = require('cypress');

module.exports = defineConfig({
    e2e: {
        // php artisan serve --port=8001, con la BD de hotel_fesc ya migrada y seedeada.
        baseUrl: 'http://127.0.0.1:8001',
        supportFile: false,
    },
});
