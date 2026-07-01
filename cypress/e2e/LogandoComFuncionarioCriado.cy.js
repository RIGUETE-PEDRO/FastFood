describe('login de funcionario criado', () => {
  const nome = 'Fernando Teste Automatizado Ferrari'
  const email = Cypress.env('EMAIL_NOVO_USUARIO')
  const senha = Cypress.env('SENHA_NOVO_USUARIO')

  before(() => {
    cy.task('deleteUserFuncionario', email)
  })

  after(() => {
    cy.task('deleteUserFuncionario', email)
  })

  it('cria um funcionario pelo painel administrativo', () => {
    cy.loginAdmin()
    cy.criarFuncionarioUi({ nome, email, senha })
  })

  it('permite login com o funcionario criado', () => {
    cy.visit('/login')

    cy.get('#email').type(email)
    cy.get('#senha').type(senha)
    cy.get('#entrar').click()

    cy.location('pathname', { timeout: 10000 }).should('eq', '/admin/bem-vindo')
  })
})
