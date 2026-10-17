describe('RF02 - autenticação', () => {
  it('RF02 autentica o administrador e abre o painel', () => {
    cy.visit('/login')

    cy.task('ensureCypressAdmin').then(({ email, senha }) => {
      cy.get('#email').type(email)
      cy.get('#senha').type(senha)
      cy.get('#entrar').click()
    })

    cy.location('pathname', { timeout: 10000 }).should('eq', '/admin/bem-vindo')
  })
})
