<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;

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
        $tipos->setValue(array_keys($this->tipos));
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


    public function onReload()
    {
    }


    /**
     * class Start game
     * @param array $param
     * @return void
     */
    public function onStart($param = null){

        $data = $this->form->getData();
        $qtpares = $data->quantidade_cartas;
        $tipos = $data->check_tipos;

        if($qtpares <= 0){
            new TMessage('error', 'Por favor, informe a quantidade de pares de cartas');
        }
        if(isset($tipos) && count($tipos) <= 0){
            new TMessage('error', 'Por favor, selecione pelo menos um tipo de pokemon');
        }
        var_dump($qtpares);
        var_dump($tipos);

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
