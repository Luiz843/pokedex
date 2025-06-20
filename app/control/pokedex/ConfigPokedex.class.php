<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * @author luiz.polli <lcpolli@ucs.br>
 * @version 1.0.0
 *
 * 09/06/2025 - Luiz Carlos Polli
 * Classe criada para configurações e ajustes da API.
 */


class ConfigPokedex extends TPage
{

    const DESC = 'Atualização da API';

    const VERS = '1.0.0';

    private $form; // Formulário de cadastro

    public function __construct()
    {
        parent::__construct();

        // Cria o formulário
        $form = new BootstrapFormBuilder('form_pokemon');
        $this->form = $form;

        // label atualiza tipo
        $label_atualiza_tipo = new TLabel('<b>Atualizar tipos:</b>');
        $label_atualiza_tipo->style = 'font-size: 20px; margin-bottom: 20px; margin-top: 2px;';

        // botao atualiza tipo
        $typeUpdate = new TButton('btn_atualiza_tipo');
        $typeUpdate->setLabel('Atualizar');
        $typeUpdate->setAction(new TAction([$this, 'atualizaTipo']), 'Sincronizar Tipos');
        $typeUpdate->class = 'btn btn-primary';
        $typeUpdate->style = 'border-radius: 5px; border: 1px solid #000000;';

        // label atualiza pokemon
        $label_atualiza_pokemon = new TLabel('<b>Atualizar pokemons:</b>');
        $label_atualiza_pokemon->style = 'font-size: 20px; margin-bottom: 20px; margin-top: 2px;';

        // botao atualiza pokemon
        $pokemonUpdate = new TButton('btn_atualiza_pokemon');
        $pokemonUpdate->setLabel('Atualizar');
        $pokemonUpdate->setAction(new TAction([$this, 'atualizaPokemon']), 'Sincronizar Pokemons');
        $pokemonUpdate->class = 'btn btn-primary';
        $pokemonUpdate->style = 'border-radius: 5px; border: 1px solid #000000;';

        // Adiciona os campos ao formulário
        $row = $this->form->addFields([$label_atualiza_tipo], [$typeUpdate]);
        $row->layout = ['col-sm-3', 'col-sm-3'];

        $row = $this->form->addFields([$label_atualiza_pokemon], [$pokemonUpdate]);
        $row->layout = ['col-sm-3', 'col-sm-3'];


        parent::add($form);
    }


    /**
     * Método atualizaPokemon
     * Atualiza via API os dados do banco de dados
     */
    public function atualizaPokemon()
    {
        $atualizaPokemon = new PokemonsApi();
        $atualizaPokemon->onAtualizaPokemon();
    }


    /**
     * Método atualizaTipo
     * Atualiza via API os tipos de Pokémon no banco de dados
     */
    public function atualizaTipo()
    {
        $atualizaTipo = new TipoPokemonApi();
        $atualizaTipo->onAtualizaTipo();
    }


    /**
     * Método que recupera o id da url e retorna caminho da imagem
     * @param string $param
     * @return string
     */
    public function getImagePath($param)
    {
        $string = $param;
        $string = str_replace('https://pokeapi.co/api/v2/pokemon/', '', $string);
        $path = 'images/pokemons/poke_' . $string . '.gif';
        return $path;
    }


    /**
     * Método show
     * Exibe o formulário na tela
     */
    public function show()
    {
        // Exibe o formulário
        parent::show();
    }


    /**
     * Método onReload
     * Recarrega o formulário
     * @param array $param
     */
    public function onReload($param = null)
    {
        // Recarrega o formulário com os parâmetros fornecidos
        if ($param) {
            // Aqui você pode adicionar lógica para recarregar o formulário com base nos parâmetros
        }

        // Exibe o formulário atualizado
        $this->show();
    }
}
