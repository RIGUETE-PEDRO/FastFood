describe('login invalido', () => {
  it('mantem o usuario na tela de login quando as credenciais estao erradas', () => {
    cy.visit('/login')

    cy.get('#email').type('usuario-inexistente@flashfood.test')
    cy.get('#senha').type('senha-errada')
    cy.get('#entrar').click()

    cy.location('pathname', { timeout: 10000 }).should('eq', '/login')
  })
})
