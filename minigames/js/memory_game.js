let gameData = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎮 Carregando jogo...');
    loadGame();
});

function getGameData(){
    try{
        const qtpares = localStorage.getItem('game_qtpares');
        const tipos = localStorage.getItem('game_tipos');
        const pokemons = localStorage.getItem('game_pokemons');

        if(qtpares && tipos && pokemons) {
            return {
                qtpares: parseInt(qtpares, 10),
                tipos: JSON.parse(tipos),
                pokemons: JSON.parse(pokemons)
            };
        } else {
            return null;
        }
    }catch(e){
        console.error('Erro ao obter dados do jogo:', e);
    }

}

function loadGame() {
    // Carregar dados
    gameData = getGameData();

    if (!gameData) {
        alert('Erro: Dados do jogo não encontrados! Redirecionando...');
        window.location.href = '../../index.php?class=StartGameMemoria';
        return;
    }else{
        console.log('Dados do jogo carregados com sucesso:', gameData);
    }

    console.log('📦 Dados carregados:', gameData);

    // Atualizar interface
    updateStats();
    createGameBoard();
}

function updateStats() {
    document.getElementById('total-pairs').textContent = gameData.qtpares;
    document.getElementById('found-pairs').textContent = '0';
    document.getElementById('remaining-pairs').textContent = gameData.qtpares;
    document.getElementById('score').textContent = '0';
}

function createGameBoard() {
    const board = document.getElementById('game-board');

    // Calcular dimensões do grid
    const totalCards = gameData.qtpares * 2; // cada par = 2 cartas
    const cols = Math.ceil(Math.sqrt(totalCards));
    const rows = Math.ceil(totalCards / cols);

    // Configurar CSS Grid
    board.style.gridTemplateColumns = `repeat(${cols}, 100px)`;
    board.style.gridTemplateRows = `repeat(${rows}, 100px)`;

    console.log(`🎲 Criando tabuleiro ${cols}x${rows} para ${totalCards} cartas`);

    // Por enquanto, criar cartas temporárias para teste
    board.innerHTML = '';
    for(let i = 0; i < totalCards; i++) {
        const card = document.createElement('div');
        card.className = 'memory-card';
        card.textContent = '?';
        card.style.background = '#fff';
        card.style.border = '2px solid #333';
        card.style.borderRadius = '8px';
        card.style.display = 'flex';
        card.style.alignItems = 'center';
        card.style.justifyContent = 'center';
        card.style.cursor = 'pointer';
        card.style.fontSize = '2rem';

        board.appendChild(card);
    }
}