describe('fluxo de compra e entrega', () => {
  const cliente = {
    nome: 'Cliente Cypress Delivery',
    email: 'cliente.delivery.cypress@flashfood.test',
    senha: 'TESTE123',
    telefone: '11999999999',
  }

  const produtoNome = 'Combo Cypress Delivery'

  let produto
  let pedidoId

  function avancarPedido(pedidoId) {
    cy.csrfToken('/Pedidos_Administrativo').then((token) => {
      cy.request({
        method: 'POST',
        url: `/Pedidos/Administrativo/${pedidoId}/avancar`,
        form: true,
        followRedirect: false,
        failOnStatusCode: false,
        body: {
          _token: token,
        },
      }).then((resp) => {
        expect([200, 302, 303]).to.include(resp.status)
      })
    })
  }

  function alterarEntrega(pedidoId, acao) {
    cy.csrfToken('/entregas').then((token) => {
      cy.request({
        method: 'POST',
        url: `/entregas/${pedidoId}/${acao}`,
        form: true,
        followRedirect: false,
        failOnStatusCode: false,
        body: {
          _token: token,
        },
      }).then((resp) => {
        expect([200, 302, 303]).to.include(resp.status)
      })
    })
  }

  before(() => {
    cy.task('cleanupPedidoFlow', {
      email: cliente.email,
      produtoNome,
    })

    cy.task('ensureOrderFlowBaseData')

    cy.task('createCheckoutProduct', {
      nome: produtoNome,
      preco: '18.90',
      descricao: 'Produto usado no fluxo automatizado de delivery',
    }).then((produtoCriado) => {
      produto = produtoCriado
    })
  })

  after(() => {
    cy.task('cleanupPedidoFlow', {
      email: cliente.email,
      produtoNome,
    })
  })

  it('compra produto, avanca o pedido e finaliza a entrega', () => {
    cy.clearCookies()
    cy.clearLocalStorage()

    cy.registrarCliente(cliente)
    cy.loginAs(cliente.email, cliente.senha)
    cy.task('ensureCustomerAddress', cliente.email)

    cy.adicionarProdutoAoCarrinho({
      produtoId: produto.id,
      quantidade: 2,
      observacao: 'Pedido criado pelo teste de compra e entrega',
    })

    cy.visit('/carrinho')

    cy.contains('td.cart-product-name', produtoNome)
      .parents('tr')
      .as('linhaCarrinho')

    cy.get('@linhaCarrinho').within(() => {
      cy.get('.input-quantidade').should('have.value', '2')
      cy.get('[data-cart-select]').check({ force: true })
    })

    cy.get('#btnFinalizarCompra').should('not.be.disabled').click()

    cy.get('#finalizarModal').should('have.class', 'is-open')
    cy.get('#tipoEntregaForm').within(() => {
      cy.get('input[name="tipo_entrega"][value="entrega"]').check({ force: true })
      cy.contains('button', 'Continuar').click()
    })

    cy.get('#enderecoModal')
      .should('have.class', 'is-open')
      .and('contain', 'Rua Cypress Delivery')

    cy.get('#enderecoForm .ff-modal__footer button[type="submit"]').click()

    cy.get('#pagamentoModal', { timeout: 10000 }).should('have.class', 'is-open')
    cy.get('#pagamentoForm').within(() => {
      cy.get('input[name="pagamento_metodo"][value="pix"]').check({ force: true })
      cy.get('#pagamento_observacoes').clear().type('Pagamento confirmado pelo Cypress')
      cy.get('.ff-modal__footer button[type="submit"]').click()
    })

    cy.location('pathname', { timeout: 10000 }).should('eq', '/pedidos')

    cy.contains('.pedido-card', produtoNome, { timeout: 10000 })
      .should('contain', 'PENDENTE')
      .and('contain', 'Entrega')

    cy.task('latestPedidoByCliente', cliente.email).then((pedido) => {
      expect(pedido, 'pedido criado').to.not.be.null
      expect(pedido.status).to.eq(1)
      expect(pedido.endereco_id).to.be.a('number')
      expect(pedido.produtos).to.contain(produtoNome)
      expect(pedido.valor_total).to.eq(37.8)

      pedidoId = pedido.id
    })

    cy.loginAdmin()

    cy.then(() => {
      cy.visit('/Pedidos_Administrativo')

      cy.get(`[data-pedido-id="${pedidoId}"]`, { timeout: 10000 })
        .should('have.attr', 'data-status', '1')
        .and('contain', cliente.nome)
        .and('contain', produtoNome)

      cy.get(`[data-pedido-id="${pedidoId}"]`)
        .contains('button', 'Avancar para Em preparo')
        .should('be.visible')

      avancarPedido(pedidoId)
      cy.visit('/Pedidos_Administrativo')

      cy.get(`[data-pedido-id="${pedidoId}"]`, { timeout: 10000 })
        .should('have.attr', 'data-status', '2')
        .and('contain', 'Em preparo')

      cy.get(`[data-pedido-id="${pedidoId}"]`)
        .contains('button', 'Avancar para A caminho')
        .should('be.visible')

      avancarPedido(pedidoId)
      cy.visit('/Pedidos_Administrativo')

      cy.get(`[data-pedido-id="${pedidoId}"]`, { timeout: 10000 })
        .should('have.attr', 'data-status', '3')
        .and('contain', 'A caminho')

      cy.visit('/entregas')

      cy.contains('.entregas-card', `Pedido #${pedidoId}`, { timeout: 10000 })
        .should('contain', 'A CAMINHO')
        .and('contain', cliente.nome)
        .and('contain', 'Rua Cypress Delivery')
        .within(() => {
          cy.contains('button', 'Aceitar entrega').should('be.visible')
        })

      alterarEntrega(pedidoId, 'aceitar')
      cy.visit('/entregas')

      cy.contains('.entregas-card', `Pedido #${pedidoId}`, { timeout: 10000 })
        .should('contain', 'Administrador')
        .within(() => {
          cy.contains('button', 'Finalizar entrega').should('be.visible')
        })

      alterarEntrega(pedidoId, 'finalizar')
      cy.visit('/entregas')

      cy.contains('.acordeao-pedidos__gatilho', 'Entregas finalizadas').click()

      cy.get('#entregasFinalizadas').should('not.have.attr', 'hidden')
      cy.contains('#entregasFinalizadas .pedido-card', `Pedido #${pedidoId}`, { timeout: 10000 })
        .should('contain', 'ENTREGUE')
        .and('contain', 'Administrador')
    })

    cy.task('latestPedidoByCliente', cliente.email).then((pedido) => {
      expect(pedido.status).to.eq(4)
      expect(pedido.motoboy_id).to.be.a('number')
    })

    cy.then(() => {
      expect(pedidoId, 'id do pedido criado').to.be.a('number')

      cy.loginAs(cliente.email, cliente.senha)
      cy.visit('/pedidos')

      cy.contains('.pedido-card', `Pedido #${pedidoId}`, { timeout: 10000 })
        .should('contain', 'ENTREGUE')
        .and('contain', produtoNome)
    })
  })
})
