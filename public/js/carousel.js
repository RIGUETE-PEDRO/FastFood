/**
 * Carrossel de Produtos - Funcionalidade
 * Permite pausar na hover e clicar em produtos
 */

document.addEventListener('DOMContentLoaded', function() {
    const carouselProducts = document.querySelectorAll('.produto-card-mini');

    carouselProducts.forEach(item => {
        item.style.cursor = 'pointer';

        item.addEventListener('click', function(e) {
            // Pega os dados do produto
            const titulo = this.querySelector('.mini-titulo').textContent;
            const preco = this.querySelector('.mini-preco').textContent;
            const imagem = this.querySelector('img').src;

            console.log('🛒 Produto clicado:', {
                titulo: titulo.trim(),
                preco: preco.trim(),
                imagem: imagem
            });

            // Aqui você pode adicionar lógica para:
            // - Abrir modal do produto
            // - Adicionar ao carrinho
            // - Levar para página do produto, etc
        });
    });

    // Log de status do carrossel
    const carouselTrack = document.querySelector('.carousel-track');
    if (carouselTrack) {
        const itemCount = document.querySelectorAll('.produto-card-mini').length / 2;
        console.log('🎠 Carrossel iniciado com', Math.floor(itemCount), 'produtos (duplicados para loop infinito)');
    }
});

