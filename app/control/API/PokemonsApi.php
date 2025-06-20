<?php

use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

/**
 * @author luiz.polli <lcpolli@ucs.br>
 * @version 1.0.0
 *
 * 09/06/2025 - Luiz Carlos Polli
 * Classe criada para configurações e ajustes da API.
 */


class PokemonsApi
{

    const DESC = 'Atualização da API';

    const VERS = '1.0.0';

    /**
     * Método atualizaPokemon
     * Atualiza via API os dados do banco de dados
     */
    public function onAtualizaPokemon()
    {
        $apiUrl = 'https://pokeapi.co/api/v2/pokemon/';
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
        $count = 0;

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

            $tradutor = new TraduzirTextoApi();
            $flavorText = str_replace(["\r", "\n"], '', $tradutor->traduzirTextoCurl($flavorText));
            $pokemonObj->descricao = $flavorText;

            $urlTypePokemon = "https://pokeapi.co/api/v2/pokemon/{$pokemonObj->name}";
            $typePokemon = json_decode(file_get_contents($urlTypePokemon), true);
            $type = $typePokemon['types'][0]['type']['name'];

            TTransaction::open('pokedex');
            $temTipo = Tipo::where('name', '=', $type)->first()->id;
            if($temTipo){
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
        }
        if($count){
            new TMessage('info', " {$count} não foram atualizados! " . $e->getMessage());
        }
        new TMessage('info', 'Pokémons atualizados com sucesso!');
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
     * Método que retorna o ID da API do Pokémon
     */
    public function getPokemonApiId($param)
    {
        $string = $param;
        $string = str_replace('https://pokeapi.co/api/v2/pokemon/', '', $string);
        $string = str_replace('/', '', $string);
        return (int) $string;
    }
}
