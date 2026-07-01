describe('login administrador', () => {
  it('autentica e redireciona para o painel', () => {
    cy.visit('/login')

    cy.get('#email').type(Cypress.env('USUARIO_ADMINISTRADOR'))
    cy.get('#senha').type(Cypress.env('SENHA_ADMINISTRADOR'))
    cy.get('#entrar').click()

    cy.location('pathname', { timeout: 10000 }).should('eq', '/admin/bem-vindo')
  })
})
