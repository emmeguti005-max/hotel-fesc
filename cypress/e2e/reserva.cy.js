// Flujo end-to-end de Reservas y Facturacion, de punta a punta en el navegador.
// No se ejecuto en esta sesion (sin Cypress instalado / sin tiempo dado el
// plazo de entrega); para correrlo:
//   npm install --save-dev cypress
//   php artisan serve --port=8001   (otra terminal, con hotel_fesc migrada/seedeada)
//   npx cypress run

describe('Reservas y check-in/check-out', () => {
    beforeEach(() => {
        cy.visit('/login');
        cy.get('input[name=correo]').type('recepcion@hotel.local');
        cy.get('input[name=password]').type('Usuario1');
        cy.get('form').submit();
        cy.url().should('include', '/panel/dashboard');
    });

    it('crea una reserva, hace checkin y checkout, y llega a la factura', () => {
        cy.visit('/panel/reservas/crear');

        cy.get('select[name=habitacion_id]').select(1);
        cy.get('input[name="huesped[nombre]"]').type('Huesped E2E');
        cy.get('input[name="huesped[documento]"]').type('999888777');
        cy.get('input[name=fecha_entrada]').type(new Date().toISOString().slice(0, 10));
        cy.get('input[name=fecha_salida]')
            .type(new Date(Date.now() + 86400000).toISOString().slice(0, 10));
        cy.get('input[name=adultos]').clear().type('1');
        cy.get('form').submit();

        cy.contains('Reserva creada');

        cy.contains('button', 'Hacer check-in').click();
        cy.contains('Check-in realizado');

        cy.get('select[name=forma_pago]').select('efectivo');
        cy.contains('button', 'Check-out y facturar').click();

        cy.url().should('include', '/panel/facturas/');
        cy.contains('FV-');
    });
});
