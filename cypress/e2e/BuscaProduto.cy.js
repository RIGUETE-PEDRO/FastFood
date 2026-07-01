describe('busca de produtos no admin', () => {
  const produtoAlvo = 'Busca Cypress Suco'
  const produtoOculto = 'Busca Cypress Pizza'
  const produtos = [produtoAlvo, produtoOculto]

  before(() => {
    cy.deleteProducts(produtos)
  })

  beforeEach(() => {
    cy.loginAdmin()
  })

  after(() => {
    cy.deleteProducts(produtos)
  })

  it('filtra a tabela pelo nome do produto', () => {
    cy.criarProdutoUi({
      nome: produtoAlvo,
      preco: '7.50',
      descricao: 'Produto alvo da busca',
      categoria: 'Bebidas',
    })

    cy.criarProdutoUi({
      nome: produtoOculto,
      preco: '29.90',
      descricao: 'Produto que deve sumir no filtro',
      categoria: 'Pizzas',
    })

    cy.visit('/gerenciamento_Produtos')
    cy.get('#searchInput').clear().type(produtoAlvo)

    cy.produtoRow(produtoAlvo).should('be.visible')
    cy.produtoRow(produtoOculto).should('not.be.visible')
  })
})
