// Page Object de reservas: crear, check-in y check-out.
export class ReservaPage {
    abrirFormulario() {
        cy.visit('/panel/reservas/crear');
    }

    llenar({ habitacion, nombre, documento, noches = 1 }) {
        cy.get('select[name=habitacion_id]').select(habitacion);
        cy.get('input[name="huesped[documento]"]').type(documento);
        cy.get('input[name="huesped[nombre]"]').type(nombre);

        // La fecha de hoy se toma del servidor (atributo min), no del reloj del
        // navegador, para que la prueba no falle por la diferencia de zona horaria.
        cy.get('input[name=fecha_entrada]')
            .invoke('attr', 'min')
            .then((hoy) => {
                const salida = new Date(`${hoy}T00:00:00Z`);
                salida.setUTCDate(salida.getUTCDate() + noches);

                cy.get('input[name=fecha_entrada]').type(hoy);
                cy.get('input[name=fecha_salida]').type(salida.toISOString().slice(0, 10));
            });
    }

    guardar() {
        cy.intercept('POST', '/panel/reservas').as('crearReserva');
        cy.contains('button', 'Guardar reserva').click();
        cy.wait('@crearReserva');
    }

    hacerCheckIn() {
        cy.intercept('POST', '/panel/reservas/*/checkin').as('checkin');
        cy.contains('button', 'Hacer check-in').click();
        cy.wait('@checkin');
    }

    hacerCheckOut(formaPago = 'efectivo') {
        cy.intercept('POST', '/panel/reservas/*/checkout').as('checkout');
        cy.get('select[name=forma_pago]').select(formaPago);
        cy.contains('button', 'Check-out y facturar').click(); // Cypress acepta solo el confirm()
        cy.wait('@checkout');
    }
}
