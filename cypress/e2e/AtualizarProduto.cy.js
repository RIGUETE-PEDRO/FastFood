describe('atualizacao de produto', () => {
  const produtoOriginal = 'Produto Cypress Editar'
  const produtoAtualizado = 'Produto Cypress Atualizado'

  before(() => {
    cy.task('deleteProduct', produtoOriginal)
    cy.task('deleteProduct', produtoAtualizado)
  })

  beforeEach(() => {
    cy.loginAdmin()
  })

  after(() => {
    cy.task('deleteProduct', produtoOriginal)
    cy.task('deleteProduct', produtoAtualizado)
  })

  it('atualiza os dados de um produto pelo painel administrativo', () => {
    cy.criarProdutoUi({
      nome: produtoOriginal,
      preco: '5.00',
      descricao: 'Produto antes da edicao',
      categoria: 'Bebidas',
    })

    cy.produtoRow(produtoOriginal).within(() => {
      cy.get('.btn-edit').click()
    })

    cy.get('#createProductOverlay').should('have.class', 'is-open')
    cy.get('#produto-nome').clear().type(produtoAtualizado)
    cy.get('#produto-preco').clear().type('9.90')
    cy.get('#produto-descricao').clear().type('Produto atualizado por teste')
    cy.get('#btnInativo').click()

    cy.get('#createProductOverlay').within(() => {
      cy.contains('button[type="submit"]', 'Salvar').click()
    })

    cy.produtoRow(produtoAtualizado)
      .should('be.visible')
      .and('contain', 'R$ 9,90')
      .and('contain', 'Inativo')

    cy.contains('#tableBody .nome-cell', produtoOriginal).should('not.exist')
  })
})
