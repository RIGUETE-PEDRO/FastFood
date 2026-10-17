describe('historico de mesas', () => {
  beforeEach(() => {
    cy.loginAdmin()
    cy.visit('/mesas/historico')
  })

  it('mostra os resumos, os filtros e o link de retorno para as mesas', () => {
    cy.get('main h1').should('have.text', 'Mesas fechadas')
    cy.get('.mesa-history-summary').within(() => {
      cy.contains('Fechamentos').should('be.visible')
      cy.contains('Total nesta pagina').should('be.visible')
    })

    cy.get('form[aria-label="Filtrar histórico de mesas"]').within(() => {
      cy.get('#mesa_id').should('be.visible')
      cy.get('#data_inicio').should('have.attr', 'type', 'date')
      cy.get('#data_fim').should('have.attr', 'type', 'date')
      cy.contains('button[type="submit"]', 'Filtrar').should('be.visible')
      cy.contains('a', 'Limpar')
        .should('have.attr', 'href')
        .and('match', /\/mesas\/historico$/)
    })

    cy.contains('a', 'Voltar para mesas')
      .should('have.attr', 'href')
      .and('match', /\/mesas$/)
  })

  it('aplica o intervalo de datas informado', () => {
    cy.get('#data_inicio').type('2026-10-01')
    cy.get('#data_fim').type('2026-10-17')
    cy.get('form[aria-label="Filtrar histórico de mesas"]')
      .contains('button[type="submit"]', 'Filtrar')
      .click()

    cy.location('pathname').should('eq', '/mesas/historico')
    cy.location('search').should('include', 'data_inicio=2026-10-01')
    cy.location('search').should('include', 'data_fim=2026-10-17')
    cy.get('#data_inicio').should('have.value', '2026-10-01')
    cy.get('#data_fim').should('have.value', '2026-10-17')
  })
})
