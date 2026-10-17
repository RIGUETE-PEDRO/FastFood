describe('RF11 - armazenamento e consulta de produtos', () => {
  const produtoNome = 'Coca-cola Teste'

  before(() => {
    cy.task('deleteProduct', produtoNome)
  })

  beforeEach(() => {
    cy.loginAdmin()
  })

  after(() => {
    cy.task('deleteProduct', produtoNome)
  })

  it('RF11 salva o produto e recupera seus dados na listagem', () => {
    cy.visit('/Administrativo')
    cy.location('pathname').should('include', '/Administrativo')

    cy.criarProdutoUi({
      nome: produtoNome,
      preco: '5.00',
      descricao: 'Refrigerante de cola',
      categoria: 'Bebidas',
    })
  })
})
