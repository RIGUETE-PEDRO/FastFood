describe('cadastro e personalizacao de pizzas', () => {
  const pizzaComPrecos = 'Pizza Cypress Precos por Tamanho'
  const pizzaComBordas = 'Pizza Cypress Bordas Automaticas'
  const produtosDeTeste = [pizzaComPrecos, pizzaComBordas]

  beforeEach(() => {
    cy.deleteProducts(produtosDeTeste)
    cy.loginCypressAdmin()
  })

  afterEach(() => {
    cy.deleteProducts(produtosDeTeste)
    cy.task('deleteUser', 'admin.cypress@flashfood.test')
  })

  it('exibe os preços por tamanho alinhados e remove a escolha individual de bordas', () => {
    cy.visit('/gerenciamento_Produtos')
    cy.get('#openCreateProduct').click()

    cy.get('#pizzaSizePrices').should('not.be.visible')
    cy.get('#produto-categoria').select('Pizzas')
    cy.get('#pizzaSizePrices').should('be.visible')
    cy.get('input[name="borda_ids[]"]').should('not.exist')
    cy.contains('#pizzaSizePrices', 'Todas as bordas ativas ficam disponíveis automaticamente')
      .should('be.visible')

    cy.get('.product-options__sizes .product-options__size')
      .should('have.length', 5)
      .each(($tamanho, indice) => {
        const tamanhos = ['P', 'M', 'G', 'GG', 'XG']
        cy.wrap($tamanho)
          .find('input')
          .should('have.attr', 'id', `pizza-preco-${tamanhos[indice]}`)
      })

    cy.get('.product-options__sizes').then(($grade) => {
      const colunas = getComputedStyle($grade[0]).gridTemplateColumns.trim().split(/\s+/)
      expect(colunas).to.have.length(5)
    })

    cy.viewport(390, 844)
    cy.get('.product-options__sizes').then(($grade) => {
      const colunas = getComputedStyle($grade[0]).gridTemplateColumns.trim().split(/\s+/)
      expect(colunas).to.have.length(2)
    })

    cy.get('#produto-categoria').select('Bebidas')
    cy.get('#pizzaSizePrices').should('not.be.visible')
  })

  it('salva preços por tamanho e mantém os valores ao editar a pizza', () => {
    cy.criarProdutoUi({
      nome: pizzaComPrecos,
      preco: '29.90',
      descricao: 'Pizza usada no teste de preços por tamanho',
      categoria: 'Pizzas',
      precosPorTamanho: {
        P: '31.90',
        M: '42.50',
        GG: '75.00',
      },
    })

    cy.produtoRow(pizzaComPrecos).within(() => {
      cy.get('.btn-edit').click()
    })

    cy.get('#pizza-preco-P').should('have.value', '31.90')
    cy.get('#pizza-preco-M').should('have.value', '42.50')
    cy.get('#pizza-preco-G').should('have.value', '')
    cy.get('#pizza-preco-GG').should('have.value', '75.00')

    cy.get('#pizza-preco-P').clear().type('33.00')
    cy.scrollOverlayToBottom('#createProductOverlay .overlay-panel')
    cy.get('#createProductOverlay').within(() => {
      cy.contains('button[type="submit"]', 'Salvar').click()
    })

    cy.produtoRow(pizzaComPrecos).within(() => {
      cy.get('.btn-edit').click()
    })
    cy.get('#pizza-preco-P').should('have.value', '33.00')
    cy.get('#pizza-preco-M').should('have.value', '42.50')
  })

  it('disponibiliza todas as bordas ativas para a pizza no cardápio', () => {
    cy.criarProdutoUi({
      nome: pizzaComBordas,
      preco: '29.90',
      descricao: 'Pizza usada no teste de bordas automáticas',
      categoria: 'Pizzas',
      precosPorTamanho: { P: '29.90' },
    })

    cy.task('activeBorders').then((bordasAtivas) => {
      expect(bordasAtivas, 'bordas ativas cadastradas').not.to.be.empty

      cy.visit('/Pizza')
      cy.get(`.produto--interactive[data-produto-nome="${pizzaComBordas}"]`)
        .invoke('attr', 'data-bordas')
        .then((bordasJson) => {
          const idsDisponiveis = JSON.parse(bordasJson).map((borda) => Number(borda.id))
          const idsAtivos = bordasAtivas.map((borda) => Number(borda.id))

          expect(idsDisponiveis).to.deep.equal(idsAtivos)
        })
    })
  })
})
