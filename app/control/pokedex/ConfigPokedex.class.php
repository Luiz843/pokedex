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
        $typeUpdate->setAction(new TAction([$this, 'onAtualizaTipo']), 'Sincronizar Tipos');
        $typeUpdate->class = 'btn btn-primary';
        $typeUpdate->style = 'border-radius: 5px; border: 1px solid #000000;';

        // label atualiza pokemon
        $label_atualiza_pokemon = new TLabel('<b>Atualizar pokemons:</b>');
        $label_atualiza_pokemon->style = 'font-size: 20px; margin-bottom: 20px; margin-top: 2px;';

        // botao atualiza pokemon
        $pokemonUpdate = new TButton('btn_atualiza_pokemon');
        $pokemonUpdate->setLabel('Atualizar');
        $pokemonUpdate->setAction(new TAction([$this, 'onAtualizaPokemon']), 'Sincronizar Pokemons');
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
     * Método atualizaTipoPokemon
     * Atualiza via API os dados do banco de dados
     */
    public function onAtualizaTipo()
    {
        $apiUrl = 'https://pokeapi.co/api/v2/type';
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($ch);
        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($returnCode !== 200) {
            throw new Exception("Erro ao acessar a API: {$returnCode}");
            die();
        }

        $cor = require __DIR__ . '/../../resources/colors_by_type.php';

        TTransaction::open('pokedex');

        foreach ($data['results'] as $tipo) {
            $novoTipo = new Tipo();
            $novoTipo->name = $tipo['name'];


            if (array_key_exists($novoTipo->name, $cor)) {
                $novoTipo->cor = $cor[$novoTipo->name]['color'];
                $novoTipo->icone = $cor[$novoTipo->name]['icon'];
            } else {
                $novoTipo->cor = '#000000'; // preto básico
                $novoTipo->icone = 'fa-question'; // ícone de interrogação
            }

            $tipoBD = Tipo::where('name', '=', $novoTipo->name)->first();

            if (!$tipoBD) {
                try {
                    $novoTipo->store();
                } catch (Exception $e) {
                    TTransaction::rollback();
                    new TMessage('error', "Erro ao atualizar tipo {$novoTipo->name}: " . $e->getMessage());
                }
            }
        }
        TTransaction::close();
        new TMessage('info', "Tipos atualizados com sucesso!");
    }


    /**
     * Método atualizaPokemon
     * Atualiza via API os dados do banco de dados
     */
    public function onAtualizaPokemon()
    {
        $apiUrl = 'https://pokeapi.co/api/v2/pokemon/?limit=10';
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($ch);
        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($returnCode !== 200) {
            throw new Exception("Erro ao acessar a API: {$returnCode}");
            die();
        }

        foreach ($data['results'] as $pokemon) {
            $pokemonObj = new Pokemon();
            $pokemonObj->name = $pokemon['name'];
            $pokemonObj->url = $pokemon['url'];

            $apiImageUrl = "https://pokeapi.co/api/v2/pokemon-species/{$pokemonObj->name}";
            $apiImageResponse = file_get_contents($apiImageUrl);
            $apiImageData = json_decode($apiImageResponse, true);

            var_dump($apiImageData);
            die();
        }
        //     // Verifica se o Pokémon já existe no banco de dados
        //     $pokemonBD = Pokemon::where('name', '=', $pokemonObj->name)->first();

        //     if (!$pokemonBD) {
        //         try {
        //             $pokemonObj->store();
        //         } catch (Exception $e) {
        //             TTransaction::rollback();
        //             new TMessage('error', "Erro ao atualizar Pokémon {$pokemonObj->name}: " . $e->getMessage());
        //         }
        //     }

        // TTransaction::close();
        // new TMessage('info', "Pokémons atualizados com sucesso!");

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
