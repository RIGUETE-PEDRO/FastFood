describe('RF06 - gerenciamento do carrinho', () => {
  const cliente = {
    nome: 'Cliente Cypress Carrinho',
    email: 'rf06.carrinho@flashfood.test',
    senha: 'TesteCarrinho123',
    telefone: '11999999996',
  }
  const produtoNome = 'Produto Cypress RF06 Carrinho'

  const limparDados = () => cy.task('cleanupPedidoFlow', {
    email: cliente.email,
    produtoNome,
    categoriaNome: 'Categoria Cypress Delivery',
    cidadeNome: 'Cidade Cypress Delivery',
  })

  before(() => {
    limparDados()
    cy.task('ensureOrderFlowBaseData')
    cy.task('createCheckoutProduct', {
      nome: produtoNome,
      preco: '7.50',
      descricao: 'Produto usado no teste de gerenciamento do carrinho',
      categoria: 'Bebidas',
    })
  })

  after(() => limparDados())

  it('RF06 adiciona, altera a quantidade e remove um produto', () => {
    cy.clearCookies()
    cy.clearLocalStorage()
    cy.registrarCliente(cliente)
    cy.loginAs(cliente.email, cliente.senha)
    cy.visit('/Bebidas')

    cy.get(`.produto[data-produto-nome="${produtoNome}"] .button-adicionar`).click()
    cy.get('#addToCartModal').should('be.visible')
    cy.get('#cart_quantidade').clear().type('2')
    cy.get('#cart_observacao').type('Sem gelo')
    cy.get('#addToCartModal').contains('button', 'Confirmar').click()

    cy.location('pathname', { timeout: 10000 }).should('eq', '/Bebidas')
    cy.visit('/carrinho')

    cy.contains('td.cart-product-name', produtoNome)
      .parents('tr')
      .find('.input-quantidade')
      .should('have.value', '2')

    cy.contains('td.cart-product-name', produtoNome)
      .parents('tr')
      .find('.input-quantidade')
      .clear()
      .type('3')
      .blur()

    cy.contains('td.cart-product-name', produtoNome)
      .parents('tr')
      .find('.input-quantidade')
      .should('have.value', '3')

    cy.contains('td.cart-product-name', produtoNome)
      .parents('tr')
      .find('.cart-line-total')
      .should('contain', 'R$ 22,50')

    cy.contains('td.cart-product-name', produtoNome)
      .parents('tr')
      .contains('button', 'Remover')
      .click()

    cy.contains('.empty-state h3', 'Nenhum produto encontrado').should('be.visible')
  })
})
