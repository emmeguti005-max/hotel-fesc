// CP-31. Flujo de punta a punta en el navegador. Se ejecuta con: npm run suite
import { LoginPage } from '../support/pages/LoginPage';
import { ReservaPage } from '../support/pages/ReservaPage';

describe('Reservas: del formulario a la factura', () => {
    const login = new LoginPage();
    const reservas = new ReservaPage();

    // Un documento distinto en cada prueba: ninguna depende de los datos de otra.
    const documentoNuevo = () => String(Date.now());

    beforeEach(() => {
        login.entrarComo('recepcion@hotel.local');
    });

    it('crea la reserva, hace check-in y check-out, y llega a la factura', () => {
        reservas.abrirFormulario();
        reservas.llenar({ habitacion: 1, nombre: 'Huesped E2E', documento: documentoNuevo() });
        reservas.guardar();
        cy.contains('Reserva creada.').should('be.visible');

        reservas.hacerCheckIn();
        cy.contains('Check-in realizado').should('be.visible');

        reservas.hacerCheckOut();
        cy.location('pathname').should('match', /\/panel\/facturas\/\d+$/);
        cy.contains(/FV-\d{6}/).should('be.visible');
    });

    it('rechaza una segunda reserva de la misma habitación en esas fechas', () => {
        reservas.abrirFormulario();
        reservas.llenar({ habitacion: 2, nombre: 'Primer huesped', documento: documentoNuevo() });
        reservas.guardar();
        cy.contains('Reserva creada.').should('be.visible');

        reservas.abrirFormulario();
        reservas.llenar({ habitacion: 2, nombre: 'Segundo huesped', documento: `${documentoNuevo()}9` });
        reservas.guardar();
        cy.contains('ya está reservada en esas fechas').should('be.visible');
    });
});
