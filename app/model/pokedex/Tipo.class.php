<?php

use Adianti\Database\TRecord;

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
}
