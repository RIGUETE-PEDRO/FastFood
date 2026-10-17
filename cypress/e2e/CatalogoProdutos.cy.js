describe('RF05 - produtos disponíveis no catálogo do cliente', () => {
  const produtoNome = 'Produto Cypress RF05 Catalogo'

  before(() => cy.task('deleteProduct', produtoNome))
  beforeEach(() => cy.loginCypressAdmin())
  after(() => cy.task('deleteProduct', produtoNome))

  it('RF05 exibe no catálogo o produto ativo cadastrado', () => {
    cy.criarProdutoUi({
      nome: produtoNome,
      preco: '7.50',
      descricao: 'Produto ativo usado para validar o catálogo',
      categoria: 'Bebidas',
    })

    cy.clearCookies()
    cy.clearLocalStorage()
    cy.visit('/Bebidas')

    cy.get(`.produto[data-produto-nome="${produtoNome}"]`)
      .should('be.visible')
      .and('contain', 'R$ 7,50')
      .and('contain', 'Produto ativo usado para validar o catálogo')
  })
})
