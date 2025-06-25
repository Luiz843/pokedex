<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TCardView;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * @author luiz.polli <
 *
 * */

class PokemonList extends TPage
{

    // Descrição do programa
    const DESC = 'Pokemons';

    // versão do programa
    const VERS = '1.00.00';

    private $pokemons = [];         // array de objetos dos cards
    private $tipos = [];            // array de objetos dos tipos
    private $form;                 // formulário de pesquisa
    private $cards;               // cards de pokemons
    private $panel_cards;          // painel de cards
    private $scroll_wrapper; // elemento de scroll para os cards
    private $current_page = 1; // página atual para paginação
    private $per_page = 4; // quantidade de pokemons por página
    private $total_pages = 1; // total de páginas para paginação

    public function __construct()
    {
        parent::__construct();

        TPage::include_css('app/resources/style-form-pokemon.css');

        $form = new BootstrapFormBuilder('form_pokemon_list');
        $this->form = $form;

        $id = new TDBCombo(
            'id',
            'pokedex',
            'Pokemon',
            'id',
            'id',
            'id'
        );
        $id_label = new TLabel('<b>ID: </b>');
        $id_label->setFontSize('20px');
        $id_label->setProperty('for', $id->getId(), true);

        $name = new TDBCombo(
            'name',
            'pokedex',
            'Pokemon',
            'id',
            'name',
            'name'
        );
        $name_label = new TLabel('<b>Nome: </b>');
        $name_label->setFontSize('20px');
        $name_label->setProperty('for', $name->getId(), true);

        $tipo = new TDBCombo(
            'tipo_id',
            'pokedex',
            'Tipo',
            'id',
            'name',
            'name'
        );
        $tipo_label = new TLabel('<b>Tipo: </b>');
        $tipo_label->setFontSize('20px');
        $tipo_label->setProperty('for', $tipo->getId(), true);

        $bt_reload = new TButton('bt_reload');
        $bt_reload->setAction(new TAction([$this, 'onReload']), 'Pesquisar');
        $bt_reload->setImage('fa:refresh');
        $bt_reload->setLabel('Recarregar');
        $bt_reload->class = 'btn btn-default';
        $bt_reload->style = 'border-radius: 5px; border: 1px solid #000000;';

        $this->form->addFields([$id_label], [$id], [$name_label], [$name], [$tipo_label], [$tipo]);
        $row = $this->form->addFields([], [$bt_reload]);
        $row->layout = ['col-sm-10', 'col-sm-2'];

        $this->scroll_wrapper = new TElement('div');
        // $scroll_wrapper->style = 'overflow-x: scroll;';
        $this->scroll_wrapper->add($this->cards);

        // painel de cards
        $this->panel_cards = new TPanelGroup();
        $this->panel_cards->style = "padding: 50px; background: url('images/forest.jpg') no-repeat center center; background-size: cover;";
        $this->panel_cards->add($this->form);
        $this->panel_cards->add($this->scroll_wrapper);


        // TScript::create("
        //     let cards = document.querySelectorAll('.card-flipped');
        //     cards.forEach(function(card) {
        //         card.addEventListener('click', function() {
        //             const front = card.querySelector('.pokemon-card-front');
        //             const back = card.querySelector('.pokemon-card-back');
        //             if (front.style.display !== 'none') {
        //                 front.style.display = 'none';
        //                 back.style.display = 'block';
        //             } else {
        //                 front.style.display = 'block';
        //                 back.style.display = 'none';
        //             }
        //         });
        //     });
        // ");

        // script para forçar layout horizontal
        // TScript::create("
        //     document.querySelectorAll('.card-wrapper').forEach(function(wrapper) {
        //         wrapper.style.minWidth = 'max-content';
        //     });
        // ");

        // container final
        $conteiner = new TVBox();
        $conteiner->style = 'width: 100%; background: linear-gradient(to bottom, #fa9d9d, #f85f5f); padding: 20px;';
        $conteiner->add($this->panel_cards);
        parent::add($conteiner);
        // $this->onReload();
    }


    public function onReload($param)
    {
        try {
            $flag = false;

            $id_param = isset($param['id']) ? $param['id'] : '';
            $name_param = isset($param['name']) ? $param['name'] : '';
            $tipo_param = isset($param['tipo_id']) ? $param['tipo_id'] : '';

            TTransaction::open('pokedex');

            // Carregar tipos antes de tudo
            $repoTipo = new TRepository('Tipo');
            $this->tipos = $repoTipo->load(new TCriteria);

            $criteria = new TCriteria();

            if (isset($param['id']) and $param['id']) {
                $criteria->add(new TFilter('id', '=', $param['id']));
                $flag = true;
            }
            if (isset($param['name']) and $param['name']) {
                $criteria->add(new TFilter('id', '=', $param['name']));
                $flag = true;
            }
            if (isset($param['tipo_id']) and $param['tipo_id']) {
                $criteria->add(new TFilter('tipo_id', '=', $param['tipo_id']));
                $flag = true;
            }
            if (!$flag) {
                $criteria->add(new TFilter('id', 'IN', [18, 19, 20, 22]));
            }

            $repo = new TRepository('Pokemon');

            $count = (int) $repo->count($criteria, FALSE);
            $this->total_pages = ceil($count / $this->per_page);

            // Paginação
            $this->per_page = 4;
            $this->current_page = isset($param['page']) ? (int)$param['page'] : 1;
            $offset = ($this->current_page - 1) * $this->per_page;
            $criteria->setProperty('limit', $this->per_page);
            $criteria->setProperty('offset', $offset);

            $this->pokemons = $repo->load($criteria);

            if (!$this->pokemons) {
                new TMessage('info', 'Nenhum Pokémon encontrado com os critérios informados.');
            } else {
                $this->cards = new TCardView();
                $this->cards->setTitleAttribute('title');
                $this->cards->setColorAttribute('color');
                $this->cards->setTemplatePath('app/view/pokemon_card.html');
                $id = 0;
                foreach ($this->pokemons as $pokemon) {
                    if (!isset($pokemon->imagem)) {
                        $pokemon->imagem = 'images/pokemon_default.png';
                    }
                    foreach ($this->tipos as $tipo) {
                        if ($pokemon->tipo_id == $tipo->id) {
                            $pokemon->color = $tipo->cor;
                            $pokemon->name_pt = $tipo->name;
                            $pokemon->icone = $tipo->icone;
                        }
                    }
                    $pokemon->isFlipped = false;
                    $pokemon->id = $id;
                    $this->cards->addItem($pokemon);
                    $id++;
                }
                $this->scroll_wrapper->add($this->cards);

                $this->form->setData((object)[
                    'id' => $id_param,
                    'name' => $name_param,
                    'tipo_id' => $tipo_param
                ]);

                // Adiciona paginação
                $pagination = new TElement('div');
                $pagination->style = 'text-align:center; margin:20px;';
                for ($i = 1; $i <= $this->total_pages; $i++) {
                    $page_link = new TElement('a');
                    $page_link->href = "index.php?class=PokemonList&page={$i}&id={$id_param}&name={$name_param}&tipo_id={$tipo_param}";
                    $page_link->style = 'margin: 0 5px; font-size:18px;';
                    $page_link->add($i);
                    if ($i == $this->current_page) {
                        $page_link->style .= 'font-weight:bold; color:#black;';
                    }
                    $pagination->add($page_link);
                }
                $this->scroll_wrapper->add($pagination);
            }


            TScript::create("
                document.querySelectorAll('.card-flipped').forEach(function(card) {
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
                $this->onReload([]);
            }
        }
        parent::show();
    }
}
