<?php

class Entrar extends Conexao {

    private $Email;
    private $Senha;
    private $Resultado;

    public function entrar($Email, $Senha) {
        $this->Email = (string) $Email;
        $this->Senha = (string) $Senha;

        $ler = new Ler();
        $ler->Leitura('usuarios', "WHERE email = :email", "email={$this->Email}");

        if ($ler->getResultado()) {
            $usuario = $ler->getResultado()[0];

            if (password_verify($this->Senha, $usuario['senha'])) {
                if ($usuario['status'] == 'S' && in_array(($usuario['nivel'] ?? 'C'), ['M', 'O', 'C'])) {
                    session_regenerate_id(true);
                    $_SESSION['sheep_user'] = $usuario;
                    $this->Resultado = true;
                } else {
                    $this->Resultado = false;
                }
            } else {
                $this->Resultado = false;
            }
        } else {
            $this->Resultado = false;
        }
    }

    public function getResultado() {
        return $this->Resultado;
    }

}
?>
