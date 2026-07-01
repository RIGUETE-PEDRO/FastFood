describe('exclusao de produto', () => {
  const produtoNome = 'Coca-cola Teste Automatizado'

  before(() => {
    cy.task('deleteProduct', produtoNome)
  })

  beforeEach(() => {
    cy.loginAdmin()
  })

  after(() => {
    cy.task('deleteProduct', produtoNome)
  })

  it('remove um produto pelo painel administrativo', () => {
    cy.criarProdutoUi({
      nome: produtoNome,
      preco: '5.00',
      descricao: 'Refrigerante de cola',
      categoria: 'Bebidas',
    })

    cy.produtoRow(produtoNome).within(() => {
      cy.get('.btn-delete').click()
    })

    cy.get('#deleteConfirmOverlay').should('have.class', 'is-open')
    cy.get('#deleteConfirmOverlay').within(() => {
      cy.contains('button[type="submit"]', 'Excluir').click()
    })

    cy.contains('#tableBody .nome-cell', produtoNome).should('not.exist')
  })
})
