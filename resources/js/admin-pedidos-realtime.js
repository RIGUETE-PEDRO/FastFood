(function () {
  function init() {
    const root = document.getElementById('pedidos-admin-root');
    if (!root) return;

    const pollingUrl = root.dataset.pollingUrl;
    if (!pollingUrl) return;

    let checksumAtual = root.dataset.checksum || '';
    let atualizandoConteudo = false;

    const badgeTotal = document.getElementById('pedidos-total-badge');
    const resumoWrapper = document.getElementById('pedidos-resumo-wrapper');
    const listaWrapper = document.getElementById('pedidos-lista-wrapper');

    const inicializarInteracoes = () => {
      const gatilhos = document.querySelectorAll('.acordeao-pedidos__gatilho');

      gatilhos.forEach((botao) => {
        const seletor = botao.dataset.target;
        const conteudo = seletor ? document.querySelector(seletor) : null;

        if (!conteudo) {
          return;
        }

        const abrir = () => {
          botao.setAttribute('aria-expanded', 'true');
          conteudo.hidden = false;
          conteudo.classList.add('is-open');
        };

        const fechar = () => {
          botao.setAttribute('aria-expanded', 'false');
          conteudo.classList.remove('is-open');
          conteudo.hidden = true;
        };

        if (botao.dataset.enhanced !== '1') {
          botao.dataset.enhanced = '1';
          botao.addEventListener('click', () => {
            const expandido = botao.getAttribute('aria-expanded') === 'true';
            if (expandido) {
              fechar();
            } else {
              abrir();
            }
          });
        }

        if (botao.getAttribute('aria-expanded') === 'true') {
          abrir();
        } else {
          conteudo.hidden = true;
        }
      });

      document.querySelectorAll('form[data-disable-on-submit]').forEach((form) => {
        if (form.dataset.enhanced === '1') {
          return;
        }

        form.dataset.enhanced = '1';
        form.addEventListener('submit', () => {
          const botao = form.querySelector('[data-avancar-button]');
          if (!botao) {
            return;
          }

          botao.classList.add('is-loading');
          botao.setAttribute('disabled', 'disabled');
        });
      });
    };

    const atualizarConteudo = async () => {
      const resposta = await fetch(`${pollingUrl}?full=1&t=${Date.now()}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        credentials: 'same-origin',
        cache: 'no-store',
      });

      if (!resposta.ok) {
        return;
      }

      const dados = await resposta.json();
      if (!dados || !dados.checksum) {
        return;
      }

      if (typeof dados.resumoHtml === 'string' && resumoWrapper) {
        resumoWrapper.innerHTML = dados.resumoHtml;
      }

      if (typeof dados.listaHtml === 'string' && listaWrapper) {
        listaWrapper.innerHTML = dados.listaHtml;
      }

      if (badgeTotal && dados.totalLabel) {
        badgeTotal.textContent = dados.totalLabel;
      }

      checksumAtual = dados.checksum;
      inicializarInteracoes();
    };

    const verificarMudancas = async () => {
      if (atualizandoConteudo || document.visibilityState !== 'visible') {
        return;
      }

      try {
        const resposta = await fetch(`${pollingUrl}?t=${Date.now()}`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
          credentials: 'same-origin',
          cache: 'no-store',
        });

        if (!resposta.ok) {
          return;
        }

        const dados = await resposta.json();
        if (!dados || !dados.checksum) {
          return;
        }

        if (checksumAtual && dados.checksum !== checksumAtual) {
          atualizandoConteudo = true;
          await atualizarConteudo();
          atualizandoConteudo = false;
        } else {
          checksumAtual = dados.checksum;
        }
      } catch (_) {
        atualizandoConteudo = false;
      }
    };

    inicializarInteracoes();
    window.setInterval(verificarMudancas, 8000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
