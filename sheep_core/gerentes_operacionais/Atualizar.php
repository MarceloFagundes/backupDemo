<?php
/**********************************************************************
 * ********************************************************************
 * 
 * ********************************************************************
 * *************sheep**PHP***********************************
 * ********************************************************************
 * TODOS OS DIREITOS RESERVADOS E CÓDIGO FONTE RASTREADO COM ARQUIVOS 
 * TODA SABEDORIA PARA CRIAR ESTES SISTEMAS VEM DO SANTO E ETERNOR PAI
 * O SANTO SENHOR DEUS DE ABRAÃO, ISSAC E JACÓ E DO MEU ÚNICO SENHOR 
 * O MESSIAS NOSSO SALVADOR, POIS A GLROIA É DO PAI E DO FILHO PARA SEMPRE
 * ********************************************************************
 */
class Atualizar extends Conexao {

    private $Banco;
    private $Dados;
    private $SQL;
    private $Locais;
    private $Resultado;

 /* * @var PDOStantement :: por .com.br */
    private $Atualizar;

 /* * @var PDO :: por */
    private $Conexao;

    //FAZ A ATUALIZAÇÃO
    public function Atualizando($Banco, array $Dados, $SQL, $Adicionais) {
        $this->Tabela = (string) $Banco;
        $this->Dados = $Dados;
        $this->Termos = (string) $SQL;
        
        parse_str($Adicionais, $this->Locais);
        $this->getSyntax();
        $this->Execute();
    }

 /** @var Retorna um Resultado de cadastro ou não :: por */
    public function getResultado() {
        return $this->Resultado;
    }

 /** @var FAZ A CONTAGEM DOS CAMPOS DA TABLEA :: por */
    public function getContaLinhas() {
        return $this->Atualizar->rowCount();
    }

    /**
     * <b>setLocais</b>
     * SERVE PARA ADICIONAR LIMIT, OFFSET E LINKS DE MANEIRA SIMPLIFICADA
     * @param STRING $Adicionais informe os links, limit e offset do BD exemplo: "name=Oliver&views=5&limit=7"
     * 
    public function setLocais($Adicionais) {
        parse_str($Adicionais, $this->Locais);
        $this->getSyntax();
        $this->Execute();
    }

    /**
     * ********** PRIVATE METHODS *************
     */

    private function Canectar() {

        $this->Conexao = parent::getCanectar();
        $this->Atualizar = $this->Conexao->prepare($this->Atualizar);
  
    }

    private function getSyntax() {
        foreach ($this->Dados as $key => $Value):
            $Locais[] = $key .  ' = :' . $key;
        endforeach;
        
        $Locais = implode(', ', $Locais);
        $this->Atualizar = "UPDATE {$this->Tabela} SET {$Locais} {$this->Termos}";
    }

    private function Execute() {
        $this->Canectar();

        try {
            $this->Atualizar->execute(array_merge($this->Dados, $this->Locais));
            $this->Resultado = true;
        } catch (Exception $wt) {
            $this->Resultado = null;
            echo "<b>Erro ao Atulizar: {$wt->getMessage()}</b> - {$wt->getCode()}" ;
        }
    }

}
