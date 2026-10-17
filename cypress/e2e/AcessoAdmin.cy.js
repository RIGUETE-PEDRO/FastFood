describe('RF03 - acesso administrativo protegido', () => {
  it('RF03 redireciona visitante sem autenticação para o login', () => {
    cy.visit('/Administrativo')

    cy.location('pathname', { timeout: 10000 }).should('eq', '/login')
  })
})
