<?php

use Adianti\Database\TConnection;
use Adianti\Control\TPage;

class TestaConexaoController extends TPage
{
    public function __construct()
    {
        parent::__construct();

        try {
            $conn = TConnection::open('pokedex');
            echo "<h1>Conexão com o banco de dados realizada com sucesso!</h1>";
            $conn = null;
        } catch (Exception $e) {
            echo "<h1>Erro ao conectar: " . $e->getMessage() . "</h1>";
        }
    }
}