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


class TipoPokemonApi
{

    const DESC = 'Atualização da API';

    const VERS = '1.0.0';

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
                    $flavorText = $novoTipo->name;
                    $tradutor = new TraduzirTextoApi();
                    $flavorText = str_replace(["\r", "\n"], '', $tradutor->traduzirTextoCurl($flavorText));
                    $novoTipo->name_pt = $flavorText;
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
}
