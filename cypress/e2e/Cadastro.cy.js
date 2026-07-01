describe('cadastro de cliente', () => {
  const email = 'teste@gmail.com'
  const senha = '123456789'

  before(() => {
    cy.task('deleteUser', email)
  })

  after(() => {
    cy.task('deleteUser', email)
  })

  it('cadastra um cliente e permite login', () => {
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
