# Hotel FESC

[![CI](https://github.com/emmeguti005-max/hotel-fesc/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/emmeguti005-max/hotel-fesc/actions/workflows/ci.yml)
[![CI develop](https://github.com/emmeguti005-max/hotel-fesc/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/emmeguti005-max/hotel-fesc/actions/workflows/ci.yml?query=branch%3Adevelop)

Sistema de gestión hotelera (habitaciones, huéspedes y reservas, aseo, restaurante con pedidos por QR y facturación) hecho en Laravel 12 + MySQL.

## Cómo correrlo

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve        # http://127.0.0.1:8000
```

## Pruebas

| Comando | Qué hace |
|---|---|
| `php artisan test` | PHPUnit (SQLite en memoria) |
| `npm run suite` | PHPUnit + Cypress con un solo comando (base SQLite propia, no toca la real) |
| `vendor/bin/pint --test` | Estilo de código |
| `vendor/bin/phpstan analyse` | Análisis estático (Larastan nivel 5) |

## Integración continua

El pipeline vive en [`.github/workflows/ci.yml`](.github/workflows/ci.yml) y corre con cada push a `main` y `develop` y con cada pull request:

1. Validar `composer.json` / `composer.lock`
2. Instalar dependencias (con caché)
3. Estilo (Pint)
4. Análisis estático (PHPStan)
5. PHPUnit con cobertura (PCOV)
6. Umbral de cobertura (`scripts/umbral-cobertura.php`, mínimo en la variable `COBERTURA_MINIMA`)
7. Publicar el reporte (HTML, Clover y JUnit) como artefacto
8. E2E con Cypress (reserva → check-in → check-out → factura)

No necesita secretos: la `APP_KEY` se genera en cada ejecución y las pruebas usan SQLite.
