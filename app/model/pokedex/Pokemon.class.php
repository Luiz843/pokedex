<?php

use Adianti\Database\TRecord;


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
}