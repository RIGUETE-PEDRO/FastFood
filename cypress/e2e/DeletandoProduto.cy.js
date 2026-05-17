describe('admin', () => {
  const baseUrl = Cypress.config('baseUrl') || 'http://localhost:8000'
  const produtonome = 'Coca-cola Teste Automatizado'

  beforeEach(() => {
    const email = Cypress.env('USUARIO_ADMINISTRADOR')
    const senha = Cypress.env('SENHA_ADMINISTRADOR')


    cy.request('GET', `${baseUrl}/login`).then((resp) => {
      const match = resp.body.match(/name="_token" value="([^"]+)"/)
      const token = match ? match[1] : null

      expect(token, 'csrf token').to.not.be.null

      cy.request({
        method: 'POST',
        url: `${baseUrl}/login`,
        form: true,
        body: {
          _token: token,
          email,
          senha,
        },
        failOnStatusCode: false,
      }).then((loginResp) => {
        expect([200, 302]).to.include(loginResp.status)
      })
    })
  })

  it('entra no admin', () => {
    cy.visit('/Administrativo')
        cy.location('pathname').should('include', '/Administrativo')
        cy.visit('/gerenciamento_Produtos')
        cy.get('#openCreateProduct').click()
        cy.get('#produto-nome').type(produtonome)
        cy.get('#produto-preco').type('5.00')
        cy.get('#produto-descricao').type('Refrigerante de cola')
        cy.get('#produto-categoria').select('Bebidas')
        cy.get('#btnAtivo').click()
        cy.contains('button', 'Salvar').click()
        cy.wait(2000)
        cy.get(`[data-nome="${produtonome}"]`).should('exist')
        cy.get(`.btn-delete[data-nome="${produtonome}"]`).click()
        cy.contains('button.btn-secondary', 'Excluir').click()
  })


})
