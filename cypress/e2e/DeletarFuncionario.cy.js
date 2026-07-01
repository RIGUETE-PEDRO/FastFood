describe('exclusao de funcionario', () => {
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

  it('remove um funcionario pelo painel administrativo', () => {
    cy.criarFuncionarioUi({ nome, email, senha })

    cy.funcionarioRow(nome).within(() => {
      cy.get('.btn-delete').click()
    })

    cy.get('#deleteConfirmOverlay').should('have.class', 'is-open')
    cy.get('#deleteConfirmOverlay').within(() => {
      cy.contains('button[type="submit"]', 'Excluir').click()
    })

    cy.contains('#tableBody .nome-cell', nome).should('not.exist')
  })
})
