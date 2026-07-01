describe('gerenciamento de funcionarios', () => {
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

  it('cria um funcionario pelo painel administrativo', () => {
    cy.criarFuncionarioUi({ nome, email, senha })
  })
})
