<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Container\TNotebook;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Util\TCardView;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapNotebookWrapper;

/**
 *
 */

class PokemonList extends TPage
{

    // Descrição do programa
    const DESC = 'Pokemons';

    // versão do programa
    const VERS = '1.00.00';

    private $pokemons = [];         // array de objetos dos cards

    public function __construct()
    {
        parent::__construct();

        TTransaction::open('pokedex');

        $repo = new TRepository('Pokemon');
        $criteria = new TCriteria();
        // $criteria->setProperty('order', 'name');
        $criteria->add(new TFilter('id', '=', '1')); // filtra apenas pokemons ativos
        $this->pokemons = $repo->load($criteria);

        $tipos = new TRepository('Tipo');
        $criteria = new TCriteria();
        $criteria->setProperty('order', 'id');
        $tipos = $tipos->load($criteria);

        TTransaction::close();

        $cards = new TCardView();
        $cards->setTitleAttribute('title');
        $cards->setColorAttribute('color');
        $cards->setTemplatePath('app/view/pokemon_card.html');
        $id = 0;
        foreach ($this->pokemons as $pokemon) {
            if(!isset($pokemon->imagem)){
                $pokemon->imagem = 'images/pokemon_default.png';
            }
            foreach ($tipos as $tipo) {
                if ($pokemon->tipo_id == $tipo->id) {
                    $pokemon->color = $tipo->cor;
                    $pokemon->name_pt = $tipo->name_pt;
                    $pokemon->icone = $tipo->icone;
                }
            }
            $pokemon->isFlipped = false;
            $pokemon->id = $id;
            $cards->addItem($pokemon);
            $id++;
        }

        $scroll_wrapper = new TElement('div');
        // $scroll_wrapper->style = 'overflow-x: scroll;';
        $scroll_wrapper->add($cards);

        // painel de cards
        $panel_cards = new TPanelGroup();
        $panel_cards->style = "padding: 50px; background: url('images/forest.jpg') no-repeat center center; background-size: cover;";
        $panel_cards->add($scroll_wrapper);


        TScript::create("
            let cards = document.querySelectorAll('.card-flipped');
            cards.forEach(function(card) {
                card.addEventListener('click', function() {
                    const front = card.querySelector('.pokemon-card-front');
                    const back = card.querySelector('.pokemon-card-back');
                    if (front.style.display !== 'none') {
                        front.style.display = 'none';
                        back.style.display = 'block';
                    } else {
                        front.style.display = 'block';
                        back.style.display = 'none';
                    }
                });
            });
        ");

        // script para forçar layout horizontal
        // TScript::create("
        //     document.querySelectorAll('.card-wrapper').forEach(function(wrapper) {
        //         wrapper.style.minWidth = 'max-content';
        //     });
        // ");

        // container final
        $conteiner = new TVBox();
        $conteiner->style = 'width: 100%; background: linear-gradient(to bottom, #fa9d9d, #f85f5f); padding: 20px;';
        $conteiner->add($panel_cards);
        parent::add($conteiner);
    }


    public function onReload($param = null)
    {
        try {
            // abre a transação com o banco
            TTransaction::open('pokedex');

            $repo = new TRepository('Pokemon');
            $criteria = new TCriteria();
            $criteria->setProperty('order', 'name');
            $this->pokemons = $repo->load($criteria);
            // fecha a transação
            TTransaction::close();

        } catch (Exception $e) {
            new TMessage('error', 'Erro ao carregar os tickets: ' . $e->getMessage());
        }
    }


    /**
     * Summary of show
     * @return void
     */
    public function show()
    {
        // checa se a datagrid foi carregada
        if (!$this->loaded and (!isset($_GET['method']) or !(in_array(
            $_GET['method'],
            array(
                'onReload'
            )
        )))) {
            if (func_num_args() > 0) {
                $this->onReload(func_get_arg(0));
            } else {
                $this->onReload();
            }
        }
        parent::show();
    }

}
