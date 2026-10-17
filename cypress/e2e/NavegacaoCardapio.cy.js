describe('navegacao do cardapio', () => {
  const paginasPublicas = [
    { caminho: '/', titulo: 'Escolha seu pedido' },
    { caminho: '/Lanches', titulo: 'Lanches' },
    { caminho: '/Pizza', titulo: 'Pizzas' },
    { caminho: '/Bebidas', titulo: 'Bebidas' },
    { caminho: '/porcao', titulo: 'Porções' },
  ]

  paginasPublicas.forEach(({ caminho, titulo }) => {
    it(`abre ${caminho} e mostra o título da página`, () => {
      cy.visit(caminho)

      cy.get('main h1').should('be.visible').and('have.text', titulo)
      cy.get('nav[aria-label="Menu principal"]').should('be.visible')
    })
  })

  it('navega do cardápio principal até a lista de bebidas', () => {
    cy.visit('/')

    cy.get('nav[aria-label="Menu principal"]')
      .contains('a.nav-link', 'Bebidas')
      .click()

    cy.location('pathname').should('eq', '/Bebidas')
    cy.get('main h1').should('have.text', 'Bebidas')
  })

  it('mostra o controle do menu lateral em telas pequenas', () => {
    cy.viewport(390, 844)
    cy.visit('/')

    cy.get('body').should('have.class', 'ff-sidebar-collapsed')
    cy.get('.ff-sidebar-toggle')
      .should('be.visible')
      .and('have.attr', 'aria-label', 'Abrir menu')
  })
})
