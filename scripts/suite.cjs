// Suite completa con un solo comando: npm run suite  (en CI: node scripts/suite.cjs --solo-e2e)
// 1) PHPUnit (SQLite en memoria)  2) Cypress contra un servidor con su propia base SQLite.
// Nunca toca la base hotel_fesc: antes de migrar comprueba que la conexion sea SQLite.
const { spawn, spawnSync } = require('node:child_process');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');

const raiz = path.resolve(__dirname, '..');
const puerto = 8001;
const baseE2e = path.join(raiz, 'database', 'e2e.sqlite');

const entornoE2e = {
    ...process.env,
    APP_ENV: 'e2e',
    DB_CONNECTION: 'sqlite',
    DB_URL: '',
    DB_DATABASE: baseE2e,
    SESSION_DRIVER: 'file',
    CACHE_STORE: 'array',
    QUEUE_CONNECTION: 'sync',
};

function correr(comando, args, env = process.env) {
    console.log(`\n> ${comando} ${args.join(' ')}`);

    return spawnSync(comando, args, { cwd: raiz, env, stdio: 'inherit', shell: process.platform === 'win32' }).status ?? 1;
}

function esperarServidor(intentos = 40) {
    return new Promise((resolve, reject) => {
        const probar = (restantes) => {
            http.get({ host: '127.0.0.1', port: puerto, path: '/login' }, (res) => {
                res.resume();
                resolve();
            }).on('error', () => {
                if (restantes === 0) {
                    reject(new Error('El servidor de pruebas no respondio.'));
                } else {
                    setTimeout(() => probar(restantes - 1), 500);
                }
            });
        };
        probar(intentos);
    });
}

function conexionEsSqlite() {
    const salida = spawnSync('php', ['artisan', 'config:show', 'database.default'], {
        cwd: raiz,
        env: entornoE2e,
        encoding: 'utf8',
    }).stdout;

    return /sqlite/.test(salida ?? '');
}

async function e2e() {
    if (!conexionEsSqlite()) {
        console.error('La conexion de E2E no es SQLite: se cancela para no borrar la base real.');

        return 1;
    }

    fs.writeFileSync(baseE2e, '');

    const preparar = correr('php', ['artisan', 'migrate:fresh', '--seeder=Database\\Seeders\\E2eSeeder', '--force', '--no-ansi'], entornoE2e);
    if (preparar !== 0) {
        return preparar;
    }

    if (!fs.existsSync(path.join(raiz, 'public', 'build', 'manifest.json')) && correr('npm', ['run', 'build']) !== 0) {
        return 1;
    }

    // php -S directo: un solo proceso, que hereda el entorno y se puede cerrar sin dejar hijos.
    // El router de Laravel toma como carpeta publica el directorio actual.
    const routerLaravel = path.join(raiz, 'vendor', 'laravel', 'framework', 'src', 'Illuminate', 'Foundation', 'resources', 'server.php');
    const servidor = spawn('php', ['-S', `127.0.0.1:${puerto}`, routerLaravel], {
        cwd: path.join(raiz, 'public'),
        env: entornoE2e,
        stdio: 'ignore',
    });

    try {
        await esperarServidor();

        return correr('npx', ['cypress', 'run'], entornoE2e);
    } finally {
        servidor.kill();
    }
}

(async () => {
    const inicio = Date.now();
    // --solo-e2e: en CI PHPUnit ya corrió en el job "calidad", aquí solo va Cypress.
    const soloE2e = process.argv.includes('--solo-e2e');
    const unitarias = soloE2e ? 0 : correr('php', ['artisan', 'test']);
    const interfaz = await e2e();
    const segundos = ((Date.now() - inicio) / 1000).toFixed(1);

    console.log(`\nPHPUnit: ${unitarias === 0 ? 'OK' : 'FALLO'} | Cypress: ${interfaz === 0 ? 'OK' : 'FALLO'} | Tiempo total: ${segundos} s`);
    process.exit(unitarias === 0 && interfaz === 0 ? 0 : 1);
})();
