describe('template spec', () => {
  it('passes', () => {
      cy.visit('http://localhost:8000/registro')
      cy.get('#name')
        .type('Teste')

      cy.get('#phone')
          .type('123456789')

      cy.get('#email')
          .type('teste@gmail.com')

      cy.get('input[name="senha"]')
          .type('123456789')

      cy.get('#password_confirmation')
          .type('123456789')

      cy.get('button[type="submit"]').click()
      cy.wait(2000)
      cy.get('a[href="/login"]').click()
       cy.get('#email')
            .type('teste@gmail.com')

          cy.get('#senha')
            .type('123456789')

          cy.get('#entrar').click()
  })
})
