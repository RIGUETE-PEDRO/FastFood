describe('admin', () => {
  const baseUrl = Cypress.config('baseUrl') || 'http://localhost:8000'

  beforeEach(() => {
    cy.request('POST', `${baseUrl}/cypress-login-admin`)
      .its('status')
      .should('eq', 200)
  })

  it('entra no admin', () => {
    cy.visit('/Administrativo')
    cy.location('pathname').should('include', '/Administrativo')
  })
})
