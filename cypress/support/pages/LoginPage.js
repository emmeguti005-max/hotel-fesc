// Page Object del inicio de sesión: si cambia el formulario, solo se toca este archivo.
export class LoginPage {
    entrarComo(correo, clave = 'Usuario1') {
        cy.intercept('POST', '/login').as('login');

        cy.visit('/login');
        cy.get('input[name=correo]').type(correo);
        cy.get('input[name=password]').type(clave, { log: false });
        cy.contains('button[type=submit]', /./).click();

        // Espera explícita a la respuesta del servidor, no un tiempo fijo.
        cy.wait('@login').its('response.statusCode').should('eq', 302);
        cy.location('pathname').should('include', '/panel');
    }
}
