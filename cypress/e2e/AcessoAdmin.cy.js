describe('acesso administrativo', () => {
  it('redireciona visitante sem login para a tela de login', () => {
    cy.visit('/Administrativo')

    cy.location('pathname', { timeout: 10000 }).should('eq', '/login')
  })
})
