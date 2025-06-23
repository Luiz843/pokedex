<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Base\TScript;
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
     * Método atualizaPokemon
     * Atualiza via API os dados do banco de dados
     */
    public function onAtualizaPokemon()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $limit = 706;
        $offset = 0;

        // Primeiro, obtenha o total de pokémons
        $apiUrlCount = "https://pokeapi.co/api/v2/pokemon?limit=1";
        $ch = curl_init($apiUrlCount);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);

        $total = isset($data['count']) ? $data['count'] : 0;
        $count = 0;

        $total = 706;

        while ($offset < $total) {
            $apiUrl = "https://pokeapi.co/api/v2/pokemon?limit={$limit}&offset={$offset}";
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

            $data = json_decode($response, true);

            if (!isset($data['results'])) {
                break;
            }

            foreach ($data['results'] as $pokemon) {
                $pokemonObj = new Pokemon();
                $pokemonObj->name = $pokemon['name'];
                $pokemonObj->imagem = $this->getImagePath($pokemon['url']);

                $apiSpeciesUrl = "https://pokeapi.co/api/v2/pokemon-species/{$pokemonObj->name}";
                $apiSpeciesResponse = file_get_contents($apiSpeciesUrl);
                $speciesUrl = json_decode($apiSpeciesResponse, true);

                $flavorText = '';
                foreach ($speciesUrl['flavor_text_entries'] as $entry) {
                    if ($entry['language']['name'] === 'en') {
                        $flavorText = $entry['flavor_text'];
                        break;
                    }
                }

                // $tradutor = new TraduzirTextoApi();
                // $flavorText = str_replace(["\r", "\n"], '', $tradutor->traduzirTextoCurl($flavorText));
                $pokemonObj->descricao = $flavorText;

                $urlTypePokemon = "https://pokeapi.co/api/v2/pokemon/{$pokemonObj->name}";
                $typePokemon = json_decode(file_get_contents($urlTypePokemon), true);
                $type = $typePokemon['types'][0]['type']['name'];

                try {

                    TTransaction::open('pokedex');
                    $temTipo = Tipo::where('name', '=', $type)->first()->id;
                    if ($temTipo) {
                        $pokemonObj->tipo_id = $temTipo;
                    } else {
                        $pokemonObj->tipo_id = 20; // Tipo padrão, caso não encontre o tipo
                    }

                    $pokemonObj->api_id = $this->getPokemonApiId($pokemon['url']);

                    $pokemonBD = Pokemon::where('name', '=', $pokemonObj->name)
                        ->where('api_id', '=', $pokemonObj->api_id)
                        ->first();

                    if (!$pokemonBD) {
                        try {
                            $pokemonObj->store();
                        } catch (Exception $e) {
                            TTransaction::rollback();
                            $count++;
                        }
                    }
                    TTransaction::close();
                } catch (Exception $e) {
                    $count++;
                    $errors[] = "Erro ao atualizar Pokémon {$pokemonObj->name}: " . $e->getMessage();
                    TTransaction::rollback();
                }
            }
            $offset += $limit;
        }
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
        $string = str_replace('/', '', $string);
        $path = 'images/pokemons/poke_' . $string . '.gif';
        return $path;
    }


    /**
     * Método que retorna o ID da API do Pokémon
     */
    public function getPokemonApiId($param)
    {
        $string = $param;
        $string = str_replace('https://pokeapi.co/api/v2/pokemon/', '', $string);
        $string = str_replace('/', '', $string);
        return (int) $string;
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
