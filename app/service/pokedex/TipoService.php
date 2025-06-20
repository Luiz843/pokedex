<?php

//Lógica para tipos e tradução

use Adianti\Database\TTransaction;

/**
 * @author luiz.polli <lcpolli@ucs.br>
 * @version 1.0
 *
 * 06/06/2025 - Luiz Carlos Polli
 * Classe criada para representar a tabela pokemon do banco de dados.
 */


class TipoService{

    public static function sincronizarTipo(){
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
        }

        // TTransaction::open('pokedex');

        $cores = include '../../app/resources/colors_by_type.php';

        foreach ($data['results'] as $tipo){
            $tipoObj = new Tipo();
            $tipoObj->nome_en = $tipo['name'];
            if($tipoObj->nome_en === $cores['name']){
                $tipoObj->cor = $cores['name']['color'];
                $tipoObj->icone = $cores['name']['icon'];
            }
        }
    }

}