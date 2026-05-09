describe('template spec', () => {
  it('passes', () => {
    cy.visit('http://localhost:8000/login')

      cy.get('#email')
          .type('admin@gmail.com')

      cy.get('#senha')
          .type('admin')

      cy.get('#entrar').click()
  })
})
