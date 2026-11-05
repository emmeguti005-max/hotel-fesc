const { defineConfig } = require('cypress');

module.exports = defineConfig({
    e2e: {
        // scripts/suite.cjs levanta este servidor con una base SQLite propia.
        baseUrl: 'http://127.0.0.1:8001',
        supportFile: false,
        video: false,
        defaultCommandTimeout: 8000,
    },
});
