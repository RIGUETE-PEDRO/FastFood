describe('admin', () => {
    const baseUrl = Cypress.config('baseUrl') || 'http://localhost:8000'
    const nome = 'Fernando Teste Automatizado Ferrari'
    const senha = Cypress.env('SENHA_NOVO_USUARIO')
    const email = Cypress.env('EMAIL_NOVO_USUARIO')
    beforeEach(() => {
        const senha = Cypress.env('SENHA_ADMINISTRADOR')
        const email = Cypress.env('USUARIO_ADMINISTRADOR')

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
    it('Criar Funcionário', () => {
        cy.visit('/gerenciamento_Funcionario')
        cy.get('#openCreateUser').click()
        cy.get('#nome').type(nome)
        cy.get('#email').type(email)
        cy.get('#telefone').type('123456789')
        cy.get('#tipo_usuario_id').select('Entregador')
        cy.get('#btnAtivoFuncionario').click()
        cy.get('#salario').type('2000')
        cy.get('#senha').type(senha)
        cy.get('#senha_confirmation').type(senha)
        cy.contains('button.btn-primary', 'Salvar').click()
        cy.wait(2000)
        cy.get(`[data-nome="${nome}"]`).should('exist')
        cy.get(`.btn-delete[data-nome="${nome}"]`).click()
        cy.wait(2000)
        cy.contains('button.btn-secondary', 'Excluir').click()
    })

})
