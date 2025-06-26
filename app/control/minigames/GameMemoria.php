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
use Adianti\Widget\Form\TCheckGroup;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TCardView;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Wrapper\TDBRadioGroup;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * @author luiz.polli <
 *
 * */

class GameMemoria extends TPage
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
        $qtcards_label = new TLabel('<b>Quantidade de cartas: </b>');
        $qtcards_label->class = 'qtcards_label';
        $qtcards_label->setProperty('for', $qtcards->getId(), true);

        TTransaction::open('pokedex');
        $this->tipos = Tipo::getTipos();
        TTransaction::close();

        $tipos = new TCheckGroup('check_tipos');
        $tipos->setLayout('horizontal');
        $tipos->addItems($this->tipos);
        $tipos->class = 'check_tipos';
        $tipos_label = new TLabel('<b>Tipos: </b>');
        $tipos_label->class = 'check_tipos_label';
        $tipos_label->setProperty('for', $tipos->getId(), true);

        $bt_reload = new TButton('bt_reload');
        $bt_reload->setAction(new TAction([$this, 'onReload']), 'Start Game');
        $bt_reload->setImage('fa-regular fa-play');
        $bt_reload->setLabel('Start Game');
        $bt_reload->class = 'btn_reload';
        // $bt_reload->style = 'border-radius: 5px; border: 1px solid #000000;';

        $row = $this->form->addFields([$qtcards_label], [$qtcards]);
        $row->layout = ['col-sm-3', 'col-sm-2'];
        $row = $this->form->addFields([$tipos_label], [$tipos]);
        $row->layout = ['col-sm-1', 'col-sm-11'];
        $row = $this->form->addFields([], [$bt_reload]);
        $row->layout = ['col-sm-10', 'col-sm-2'];


        // painel de cards
        $this->panel_cards = new TPanelGroup();
        $this->panel_cards->class = 'panel_cards';
        // $this->panel_cards->style = "padding: 50px; background: url('images/forest.jpg') no-repeat center center; background-size: cover;";
        $this->panel_cards->add($this->form);
        $this->panel_cards->add($this->scroll_wrapper);

        // container final
        $conteiner = new TVBox();
        $conteiner->style = 'width: 100%; background: linear-gradient(to bottom, #fa9d9d, #f85f5f); padding: 20px;';
        $conteiner->add($this->panel_cards);
        // $this->onReload();
        parent::add($conteiner);
    }


    public function onReload()
    {
        try {

        } catch (Exception $e) {
            new TMessage('error', 'Erro ao iniciar o game: ' . $e->getMessage());
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
