<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;

/**
 * CalendarEvent Active Record
 * @author Luiz Carlos Polli <
 * 09/06/2025 - Luiz Carlos Polli
 *    Classe criada
 */
class Tipo extends TRecord
{
    const TABLENAME = 'public.tipo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}


    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('name');
        parent::addAttribute('name_pt');
        parent::addAttribute('cor');
        parent::addAttribute('icone');
    }


    /**
     * Método que retorna array associativo com os tipos
     * @return array
     */
    public static function getTipos()
    {
        $repository = new TRepository('Tipo');
        $criteria = new TCriteria;
        $criteria->setProperty('order', 'id');
        $tipos = $repository->load($criteria, FALSE);
        $result = [];
        if ($tipos) {
            foreach ($tipos as $tipo) {
                $result[$tipo->id] = strtoupper($tipo->name);
            }
        }
        return $result;
    }


    /**
     * Método que retorna array associativo com o name, cor e icone
     * @return array
     * @param $id
     */
    public static function getTipoNameCorIcone($id){
        $repository = new TRepository('Tipo');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', '=', $id));
        $tipo = $repository->load($criteria, FALSE);

        if ($tipo) {
            $tipo = $tipo[0];
            return [
                'id'    => $tipo->id,
                'name'  => $tipo->name,
                'cor'   => $tipo->cor,
                'icone' => $tipo->icone
            ];
        } else {
            return [];
        }

    }
}
