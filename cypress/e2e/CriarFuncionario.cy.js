describe('RF01 - cadastro de funcionário', () => {
  const nome = 'Fernando Teste Automatizado Ferrari'
  const email = Cypress.env('EMAIL_NOVO_USUARIO')
  const senha = Cypress.env('SENHA_NOVO_USUARIO')

  before(() => {
    cy.task('deleteUserFuncionario', email)
  })

  beforeEach(() => {
    cy.loginAdmin()
  })

  after(() => {
    cy.task('deleteUserFuncionario', email)
  })

  it('RF01 cria um funcionário pelo painel administrativo', () => {
    cy.criarFuncionarioUi({ nome, email, senha })
  })
})
