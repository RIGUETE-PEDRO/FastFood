describe('RF01 - cadastro de cliente', () => {
  const email = 'rf01.cadastro@flashfood.test'
  const senha = '123456789'

  before(() => {
    cy.task('deleteUser', email)
  })

  after(() => {
    cy.task('deleteUser', email)
  })

  it('RF01 cria o cliente e permite autenticação posterior', () => {
    cy.visit('/registro')

    cy.get('#name').type('Teste Cypress')
    cy.get('#phone').type('123456789')
    cy.get('#email').type(email)
    cy.get('input[name="senha"]').type(senha)
    cy.get('#password_confirmation').type(senha)
    cy.contains('button[type="submit"]', 'Cadastrar').click()

    cy.location('pathname', { timeout: 10000 }).should('eq', '/login')

    cy.get('#email').type(email)
    cy.get('#senha').type(senha)
    cy.get('#entrar').click()

    cy.location('pathname', { timeout: 10000 }).should('eq', '/')
  })
})
