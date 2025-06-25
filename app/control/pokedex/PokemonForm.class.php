<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TImageCropper;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Wrapper\BootstrapFormBuilder;
use App\Model\Pokemon;

/**
 * @author luiz.polli <lcpolli@ucs.br>
 * @version 1.0.0
 *
 * 02/06/2025 - Luiz Carlos Polli
 * Classe criada para representar o formulário de cadastro de pokemons.
 */


class PokemonForm extends TPage
{

    const DESC = 'Formulário de cadastro de pokemons';

    const VERS = '1.0.0';

    private $form; // Formulário de cadastro

    public function __construct()
    {
        parent::__construct();

        // Cria o formulário
        $form = new BootstrapFormBuilder('form_pokemon');
        $this->form = $form;

        // id
        $id = new TEntry('id');
        $id->setEditable(false);
        $id->setSize('100%');
        $id_label = new TLabel('<b>ID: </b>');
        $id_label->setProperty('for', $id->getId(), true);

        // Nome
        $nome = new TEntry('nome');
        $nome->setSize('100%');
        $nome_label = new TLabel('<b>Nome: </b>');
        $nome_label->setProperty('for', $nome->getId(), true);

        // Descrição
        $descricao = new TText('descricao');
        $descricao->setSize('100%', 100);
        $descricao_label = new TLabel('<b>Descrição: </b>');
        $descricao_label->setProperty('for', $descricao->getId(), true);

        // Tipo
        $tipo = new TDBCombo('tipo', 'pokedex', 'Tipo', 'id', 'name');
        $tipo->setSize('100%');
        $tipo_label = new TLabel('<b>Tipo: </b>');
        $tipo_label->setProperty('for', $tipo->getId(), true);

        // Imagem
        $imagemCropper = new TImageCropper('imagem');
        $imagemCropper->setSize(300, 150);
        $imagemCropper->setCropSize(300, 150);
        $imagemCropper->setAllowedExtensions(['jpg', 'jpeg', 'png']);
        $imagemCropper_label = new TLabel('<b>Imagem: </b>');
        $imagemCropper_label->setProperty('for', $imagemCropper->getId(), true);

        // Cria o botão de salvar
        $save_button = $form->addAction('Salvar', new TAction([$this, 'onSend']), 'fa:save');

        $this->form->addFields([$id_label], [$id]);
        $this->form->addFields([$nome_label], [$nome]);
        $this->form->addFields([$descricao_label], [$descricao]);
        $this->form->addFields([$tipo_label], [$tipo]);
        $this->form->addFields([$imagemCropper_label], [$imagemCropper]);

        parent::add($form);
    }


    /**
     * Método onSend
     * Envia os dados do formulário para o banco de dados
     * @param array $param
     */
    public function onSend($param = null)
    {
        // Verifica se o formulário foi enviado
        if ($param) {
            try {
                TTransaction::open('pokedex');
                // Obtém os dados do formulário
                $data = $this->form->getData();

                if (!empty($data->imagem)) {
                    $nomeOriginal = basename($data->imagem);
                    $origem = $data->imagem;
                    if (!file_exists($origem) && file_exists('tmp/' . $nomeOriginal)) {
                        $origem = 'tmp/' . $nomeOriginal;
                    }
                    if (!file_exists($origem) && file_exists('/tmp/' . $nomeOriginal)) {
                        $origem = '/tmp/' . $nomeOriginal;
                    }
                    if (file_exists($origem)) {
                        $destinoDir = 'images/'; // agora na raiz do projeto
                        if (!is_dir($destinoDir)) {
                            mkdir($destinoDir, 0777, true);
                        }
                        $date = new DateTime();
                        $novoName = $date->format('Ymd_His') . '_' . $nomeOriginal;
                        $destino = $destinoDir . $novoName;
                        if (rename($origem, $destino)) {
                            $data->imagem = $destino;
                        } else {
                            $data->imagem = null;
                        }
                    } else {
                        $data->imagem = null;
                    }
                } else {
                    // Se não veio imagem nova, mantém a antiga (em edição) ou seta null (novo cadastro)
                    if (!empty($data->id)) {
                        $pokemonAntigo = new Pokemon($data->id);
                        $data->imagem = $pokemonAntigo->imagem;
                    } else {
                        $data->imagem = null;
                    }
                }

                // Cria registro no banco de dados
                $pokemon = new Pokemon($data->id);
                $pokemon->fromArray((array)$data);
                $pokemon->store();

                // Fecha a transação
                TTransaction::close();

                // Limpa o formulário
                $this->form->clear();
                new TMessage('info', 'Cadastro realizado com sucesso!');
            } catch (Exception $e) {
                // Exibe uma mensagem de erro
                new TMessage('erro', 'Erro ao salvar o pokemon: ' . $e->getMessage());
                TTransaction::rollback();
            }
        }
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
