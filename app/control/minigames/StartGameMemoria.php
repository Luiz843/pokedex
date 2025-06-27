<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TCheckGroup;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * @author luiz.polli <
 *
 * */

class StartGameMemoria extends TPage
{

    // Descrição do programa
    const DESC = 'Mini Jogo de Memória';

    // versão do programa
    const VERS = '1.00.00';

    private $tipos = [];            // array de objetos dos tipos

    public function __construct()
    {
        parent::__construct();

        TPage::include_css('app/resources/styles_gamememoria.css');

        $form = new BootstrapFormBuilder('form_pokemon_list');
        $this->form = $form;

        $qtcards = new TEntry('quantidade_cartas');
        $qtcards->class = 'qtcards';
        $qtcards_label = new TLabel('<b>Numero de pares</b>');
        $qtcards_label->class = 'qtcards_label';
        $qtcards_label->setProperty('for', $qtcards->getId(), true);

        TTransaction::open('pokedex');
        $this->tipos = Tipo::getTipos();
        TTransaction::close();

        $tipos = new TCheckGroup('check_tipos');
        $tipos->setLayout('horizontal');
        $tipos->addItems($this->tipos);
        // $tipos->setValue(array_keys($this->tipos));
        $tipos->class = 'check_tipos';
        $tipos_label = new TLabel('<b>Tipos dos pokemons</b>');
        $tipos_label->class = 'check_tipos_label';
        $tipos_label->setProperty('for', $tipos->getId(), true);

        $bt_reload = new TButton('bt_reload');
        $bt_reload->setAction(new TAction([$this, 'onStart']), 'Start Game');
        $bt_reload->setImage('fa-regular fa-play');
        $bt_reload->setLabel('Start Game');
        $bt_reload->class = 'btn_reload';
        // $bt_reload->style = 'border-radius: 5px; border: 1px solid #000000;';

        $this->form->addFields([$qtcards_label]);
        $this->form->addFields([$qtcards]);
        $this->form->addFields([$tipos_label]);
        $this->form->addFields([$tipos]);
        $this->form->addFields([$bt_reload]);

        // painel de cards
        $this->panel_cards = new TPanelGroup();
        // $this->panel_cards->style = "padding: 50px; background: url('images/forest.jpg') no-repeat center center; background-size: cover;";
        $this->panel_cards->add($this->form);
        $this->panel_cards->add($this->scroll_wrapper);

        // container final
        $conteiner = new TVBox();
        $conteiner->style = 'width: 100%; height: 100%; background: linear-gradient(to bottom, #dfeaff, #e6ecff); padding: 30px;';
        $conteiner->add($this->panel_cards);

        TScript::create('
            setTimeout(function() {
                let classform = document.querySelector(".tabpanel_form_pokemon_list");
                if (classform) {
                    classform.classList.add("panel_config_gamememoria");
                }
                let classtab = document.querySelector(".panel-body");
                if (classtab) {
                    classtab.classList.add("panelbod_gamememoria");
                }
            }, 300);

            function adicionarPokebolas() {
                const container = document.querySelector(".panel-body");
                if (!container) {
                    console.log("Container não encontrado");
                    return;
                }
                const numPokebolas = Math.floor(Math.random() * 5) + 9;
                for(let i = 0; i < numPokebolas; i++) {
                    const pokebola = document.createElement("div");
                    pokebola.className = "pokebola-bg";
                    const tamanho = Math.random() * 30 + 40;
                    pokebola.style.width = tamanho + "px";
                    pokebola.style.height = tamanho + "px";
                    const top = Math.random() * 80;
                    const left = Math.random() * 80;
                    pokebola.style.top = top + "%";
                    pokebola.style.left = left + "%";
                    pokebola.style.zIndex = "9999";
                    pokebola.style.position = "absolute";
                    pokebola.style.pointerEvents = "none";
                    container.appendChild(pokebola);
                }
            }
            setTimeout(adicionarPokebolas, 500);
        ');
        parent::add($conteiner);
    }


    public function onReload() {}


    /**
     * class Start game
     * @param array $param
     * @return void
     */
    public function onStart($param = null)
    {
        $gogame = false;
        $data = $this->form->getData();
        $qtpares = $data->quantidade_cartas;
        $tipos = $data->check_tipos;

        if ($qtpares <= 0) {
            new TMessage('error', 'Por favor, informe a quantidade de pares de cartas');
            $gogame = false;
        } else if (isset($tipos) && count($tipos) <= 0) {
            new TMessage('error', 'Por favor, selecione pelo menos um tipo de pokemon');
            $gogame = false;
        } else {
            $gogame = true;
        }

        if ($gogame) {
            // array de pokemos
            $pokemons = [];

            // abre transação
            TTransaction::open('pokedex');

            // Carregar tipos
            foreach ($tipos as $key => $tipo) {
                $tipos[$key] = Tipo::getTipoNameCorIcone($tipo);
            }

            // Verificação de segurança
            if ($qtpares > 0 && count($tipos) > 0) {

                foreach ($tipos as $key => $tipo) {
                    $totalPokemonsTipos =+ Pokemon::where('tipo_id', '=', $tipo['id'])->count();
                }
                if($totalPokemonsTipos < $qtpares) {
                    new TMessage('error', 'Não há pokémons suficientes para o número de pares selecionados.');
                    TTransaction::close();
                    return;
                }

                // 1 pokemon de cada tipo
                $pokemonsMinimos = min(count($tipos), $qtpares);
                for ($i = 0; $i < $pokemonsMinimos;) {
                    $pokemonAleatorio = Pokemon::getPokemonRandByTipo($tipos[$i]['id']);
                    if (!in_array($pokemonAleatorio, $pokemons)) {
                        $pokemons[] = $pokemonAleatorio;
                        $i++;
                    }
                }
                // completa com pokemons aleatórios
                $pokemonsRestantes = $qtpares - count($pokemons);
                for ($i = 0; $i < $pokemonsRestantes;) {
                    $tipoRandomKey = array_rand($tipos);
                    $tipoRandomId = $tipos[$tipoRandomKey]['id'];
                    $pokemonAleatorio = Pokemon::getPokemonRandByTipo($tipoRandomId);
                    if (!in_array($pokemonAleatorio, $pokemons)) {
                        $pokemons[] = $pokemonAleatorio;
                        $i++;
                    }
                }
            }
            TTransaction::close();
            // var_dump($pokemons);
            // die();

            $qtparesEscaped = (int)$qtpares;
            $tiposEscaped = json_encode($tipos);
            $pokemonsEscaped = json_encode($pokemons);
            TScript::create("
                localStorage.setItem('game_qtpares', '{$qtparesEscaped}');
                localStorage.setItem('game_tipos', '{$tiposEscaped}');
                localStorage.setItem('game_pokemons', '{$pokemonsEscaped}');
                console.log('Daados salvos!');
                window.location.href = 'minigames/html/memory_game.html';
            ");
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
