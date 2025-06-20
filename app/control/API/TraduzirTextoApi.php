<?php

/**
 * @author luiz.polli <lcpolli@ucs.br>
 * @version 1.0.1
 *
 * 09/06/2025 - Luiz Carlos Polli
 * Classe criada para configurações e ajustes da API de tradução utilizando MyMemory.
 */

class TraduzirTextoApi
{
    const DESC = 'API de tradução utilizando MyMemory';
    const VERS = '1.0.1';

    /**
     * Método traduzirTextoCurl
     * Traduz um texto usando a API de tradução MyMemory
     *
     * @param string $texto Texto a ser traduzido
     * @param string $source Idioma de origem (padrão: 'en')
     * @param string $target Idioma de destino (padrão: 'pt')
     * @return string Texto traduzido
     */
    function traduzirTextoCurl(string $texto, string $source = 'en', string $target = 'pt'): string
    {
        $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($texto) . '&langpair=' . $source . '|' . $target;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Erro na requisição cURL: ' . curl_error($ch));
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        if (isset($responseData['responseData']['translatedText'])) {
            return $responseData['responseData']['translatedText'];
        } else {
            return 'Erro ao traduzir o texto';
        }
    }
}
