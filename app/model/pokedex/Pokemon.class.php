<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;

/**
 * @author luiz.polli <lcpolli@ucs.br>
 * @version 1.0
 *
 * 02/06/2025 - Luiz Carlos Polli
 * Classe criada para representar a tabela pokemon do banco de dados.
 */


class Pokemon extends TRecord{
    const TABLENAME = 'public.pokemon';
    const IDPOLICY = 'max';
    const PRIMARYKEY = 'id';


    public function __construct($id = null, $callObjectLoad = true) {

        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('api_id');
        parent::addAttribute('name');
        parent::addAttribute('tipo_id');
        parent::addAttribute('imagem');
        parent::addAttribute('descricao');
    }


    /**
     * Método que retorna os ids dos pokemons de acordo com o tipo
     * @return array
     * @param $id
     */
    public static function getPokemonByTipo($id)
    {
        $repository = new TRepository('Pokemon');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('tipo_id', '=', $id));
        $pokemons = $repository->load($criteria, FALSE);

        $result = [];
        if ($pokemons) {
            foreach ($pokemons as $pokemon) {
                $result[] = $pokemon->id;
            }
        }
        return $result;
    }


    /**
     * Método que rece o id do tipo e retorna o id de um pokemon aleatório deste tipo
     * @param int $id
     * @return array|null
     */
    public static function getPokemonRandByTipo($id)
    {
        $repository = new TRepository('Pokemon');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('tipo_id', '=', $id));
        $pokemons = $repository->load($criteria, FALSE);
        $result = [];
        if ($pokemons) {
            foreach ($pokemons as $pokemon) {
                $result[] = [
                    'id' => $pokemon->id,
                    'name' => $pokemon->name,
                ];
            }
        }
        if (count($result) > 0) {
            // escolhe um pokemon aleatório a partir da lista de pokemons do tipo
            $pokemon = $result[array_rand($result)];
            return $pokemon;
        } else {
            // se não houver pokemons do tipo, retorna 0
            return null;
        }
    }

}