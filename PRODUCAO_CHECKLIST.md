# 🚀 CHECKLIST DE PRODUÇÃO — Mondini Delivery

> **Leia este arquivo ANTES de entregar o sistema para qualquer cliente.**
> Ele está aqui para que nenhuma IA, desenvolvedor ou você mesmo esqueça
> de configurar itens críticos antes do deploy em produção.

---

## ⚠️ ITENS OBRIGATÓRIOS ANTES DO DEPLOY

### 1. 📧 ATIVAR ENVIO DE E-MAIL POR SMTP (PHPMailer)

**Situação atual:** O sistema está em **MODO DEMO**. A verificação de e-mail
por código OTP está **DESATIVADA**. Quando um usuário faz login com conta
não verificada, ela é verificada automaticamente, sem enviar e-mail.

**O que fazer em produção:**

1. Abra o arquivo:
   `sheep_temas/site/header.php`

2. Localize o bloco com o comentário `⚠️ MODO DEMO`.

3. **REMOVA** o bloco de auto-verificação (as ~3 linhas com `conta_verificada => 1`).

4. **DESCOMENTE** o bloco `🚀 BLOCO DE PRODUÇÃO`.

5. **SUBSTITUA** o `@mail()` dentro do bloco pelo PHPMailer com SMTP real.
   O PHPMailer já está instalado em: `sheep_core/funcionarios/PHPMailer/`

   **Exemplo de configuração com Gmail:**
   ```php
   use PHPMailer\PHPMailer\PHPMailer;
   require_once __DIR__ . '/../sheep_core/funcionarios/PHPMailer/src/PHPMailer.php';
   require_once __DIR__ . '/../sheep_core/funcionarios/PHPMailer/src/SMTP.php';
   require_once __DIR__ . '/../sheep_core/funcionarios/PHPMailer/src/Exception.php';

   $mail = new PHPMailer(true);
   $mail->isSMTP();
   $mail->Host       = 'smtp.gmail.com';
   $mail->SMTPAuth   = true;
   $mail->Username   = 'SEU_EMAIL@gmail.com';        // ← TROCAR
   $mail->Password   = 'SUA_SENHA_DE_APLICATIVO';    // ← TROCAR (não é a senha normal!)
   $mail->SMTPSecure = 'tls';
   $mail->Port       = 587;
   $mail->CharSet    = 'UTF-8';
   $mail->setFrom('SEU_EMAIL@gmail.com', 'Pizzaria ' . $nome_loja);
   $mail->addAddress($usuario['email'], $usuario['nome']);
   $mail->Subject = 'Ative sua conta';
   $mail->Body    = 'Seu código: ' . $otp;
   $mail->send();
   ```

   > **IMPORTANTE:** A `$mail->Password` deve ser uma **Senha de Aplicativo** do Google,
   > não a senha da sua conta Gmail. Gere em:
   > https://myaccount.google.com/apppasswords

---

### 2. 🔑 TROCAR CREDENCIAIS DO DEMO

Antes de entregar para o cliente, certifique-se de:

- [ ] Trocar o e-mail e senha do admin no painel (`sheep_painel`)
- [ ] Remover ou redefinir o usuário de demonstração do banco de dados
- [ ] Atualizar o `nome_loja`, `logo`, `cor_primaria` nas configurações

---

### 3. 🌐 CONFIGURAR DOMÍNIO E CONSTANTES

- [ ] Definir a constante `HOME` com a URL real do cliente (ex: `https://pizzariadomundo.com.br`)
- [ ] Configurar o arquivo `.htaccess` para o domínio correto
- [ ] Testar todos os redirecionamentos após a troca de domínio

---

### 4. 💳 CONFIGURAR PAGAMENTOS (MercadoPago / PayPal)

- [ ] Substituir as chaves de **SANDBOX/TESTE** pelas chaves de **PRODUÇÃO** do MercadoPago
- [ ] Fazer o mesmo para o PayPal (Client ID e Secret de produção)
- [ ] Testar uma transação real de ponta a ponta antes da entrega

---

### 5. 🍔 CONFIGURAR INTEGRAÇÃO IFOOD

- [ ] Inserir as credenciais reais do iFood do cliente no painel (`Conexões > iFood Oficial`)
- [ ] Testar o polling automático de pedidos
- [ ] Confirmar que o webhook/callback está apontando para o domínio de produção

---

### 6. 🔒 SEGURANÇA

- [ ] Desativar `display_errors` no `php.ini` ou no `.htaccess` do cliente
- [ ] Garantir que a pasta `sheep_painel` está protegida por login
- [ ] Verificar permissões de pastas de upload

---

## 📋 COMO USAR ESTE ARQUIVO COM UMA IA

Se você estiver usando uma IA (ChatGPT, Gemini, Claude, etc.) para te ajudar
no deploy, basta mostrar este arquivo e dizer:

> "Leia o PRODUCAO_CHECKLIST.md e me ajude a completar os itens pendentes."

A IA terá todo o contexto necessário para te guiar.

---

*Criado automaticamente em 2026-05-29 | Projeto: Mondini Delivery*
