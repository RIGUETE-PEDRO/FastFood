describe('RF01/RF02 - cadastro e autenticação de funcionário', () => {
  const nome = 'Fernando Teste Automatizado Ferrari'
  const email = Cypress.env('EMAIL_NOVO_USUARIO')
  const senha = Cypress.env('SENHA_NOVO_USUARIO')

  before(() => {
    cy.task('deleteUserFuncionario', email)
  })

  after(() => {
    cy.task('deleteUserFuncionario', email)
  })

  it('RF01 cadastra o funcionário autorizado', () => {
    cy.loginAdmin()
    cy.criarFuncionarioUi({ nome, email, senha })
  })

  it('RF02 autentica o funcionário cadastrado', () => {
    cy.visit('/login')

    cy.get('#email').type(email)
    cy.get('#senha').type(senha)
    cy.get('#entrar').click()

    cy.location('pathname', { timeout: 10000 }).should('eq', '/admin/bem-vindo')
  })
})
