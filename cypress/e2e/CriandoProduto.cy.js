describe('admin', () => {
  const baseUrl = Cypress.config('baseUrl') || 'http://localhost:8000'

  beforeEach(() => {
    cy.request('POST', `${baseUrl}/cypress-login-admin`)
      .its('status')
      .should('eq', 200)
  })

  it('entra no admin', () => {
    cy.visit('/Administrativo')
        cy.location('pathname').should('include', '/Administrativo')
        cy.visit('/gerenciamento_Produtos')
        cy.get('#openCreateProduct').click()
        cy.get('#produto-nome').type('Coca-cola Teste')
        cy.get('#produto-preco').type('5.00')
        cy.get('#produto-descricao').type('Refrigerante de cola')
        cy.get('#produto-categoria').select('Bebidas')
        cy.get('#btnAtivo').click()
        cy.contains('button', 'Salvar').click()
  })

    after(() => {
        cy.task('deleteProduct', 'Coca-cola Teste')
    })
})
