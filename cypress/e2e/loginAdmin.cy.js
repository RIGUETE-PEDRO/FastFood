describe('template spec', () => {
  it('passes', () => {
    cy.visit('http://localhost:8000/login')

          cy.env(['USUARIO_ADMINISTRADOR', 'SENHA_ADMINISTRADOR']).then((env) => {
          cy.get('#email')
            .type(env.USUARIO_ADMINISTRADOR)

          cy.get('#senha')
            .type(env.SENHA_ADMINISTRADOR)

          cy.get('#entrar').click()
          })
  })
})
