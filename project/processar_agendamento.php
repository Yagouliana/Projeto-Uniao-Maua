<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'uniao_maua_futsal';
$username = 'root';
$password = '';

// Configurações de email (opcional)
$email_admin = 'contato@uniaomauafutsal.com.br';
$nome_clube = 'União Mauá Futsal';

try {
    // Conexão com o banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar se o formulário foi enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Sanitizar e validar dados
        $nome = trim($_POST['nome'] ?? '');
        $data_nascimento = $_POST['data_nascimento'] ?? '';
        $categoria = $_POST['categoria'] ?? '';
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $responsavel = trim($_POST['responsavel'] ?? '');
        $experiencia = trim($_POST['experiencia'] ?? '');
        
        // Validações básicas
        $erros = [];
        
        if (empty($nome)) {
            $erros[] = 'Nome é obrigatório';
        }
        
        if (empty($data_nascimento)) {
            $erros[] = 'Data de nascimento é obrigatória';
        }
        
        if (empty($categoria)) {
            $erros[] = 'Categoria é obrigatória';
        }
        
        if (empty($telefone)) {
            $erros[] = 'Telefone é obrigatório';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Email válido é obrigatório';
        }
        
        // Validar idade baseada na categoria
        if (!empty($data_nascimento) && !empty($categoria)) {
            $hoje = new DateTime();
            $nascimento = new DateTime($data_nascimento);
            $idade = $hoje->diff($nascimento)->y;
            
            $categorias_idades = [
                'sub-9' => [7, 9],
                'sub-11' => [9, 11],
                'sub-13' => [11, 13],
                'sub-15' => [13, 15],
                'sub-17' => [15, 17],
                'sub-20' => [17, 20]
            ];
            
            if (isset($categorias_idades[$categoria])) {
                [$idade_min, $idade_max] = $categorias_idades[$categoria];
                if ($idade < $idade_min || $idade > $idade_max) {
                    $erros[] = "Idade não corresponde à categoria $categoria (deve ter entre $idade_min e $idade_max anos)";
                }
            }
        }
        
        // Se não há erros, processar o agendamento
        if (empty($erros)) {
            
            // Verificar se já existe agendamento com mesmo email
            $stmt = $pdo->prepare("SELECT id FROM agendamentos WHERE email = ? AND status = 'pendente'");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $erros[] = 'Já existe um agendamento pendente para este email';
            } else {
                
                // Inserir agendamento no banco
                $sql = "INSERT INTO agendamentos (nome, data_nascimento, categoria, telefone, email, responsavel, experiencia, data_agendamento, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pendente')";
                
                $stmt = $pdo->prepare($sql);
                $resultado = $stmt->execute([
                    $nome,
                    $data_nascimento,
                    $categoria,
                    $telefone,
                    $email,
                    $responsavel,
                    $experiencia
                ]);
                
                if ($resultado) {
                    $agendamento_id = $pdo->lastInsertId();
                    
                    // Enviar email de confirmação (opcional)
                    enviarEmailConfirmacao($email, $nome, $categoria, $agendamento_id);
                    
                    // Redirecionar com sucesso
                    header('Location: index.html?sucesso=1');
                    exit;
                    
                } else {
                    $erros[] = 'Erro ao processar agendamento. Tente novamente.';
                }
            }
        }
        
        // Se há erros, redirecionar com erro
        if (!empty($erros)) {
            $erro_msg = implode(', ', $erros);
            header('Location: index.html?erro=' . urlencode($erro_msg));
            exit;
        }
    }
    
} catch (PDOException $e) {
    error_log('Erro no banco de dados: ' . $e->getMessage());
    header('Location: index.html?erro=' . urlencode('Erro interno. Tente novamente mais tarde.'));
    exit;
}

// Função para enviar email de confirmação
function enviarEmailConfirmacao($email_destinatario, $nome, $categoria, $agendamento_id) {
    global $email_admin, $nome_clube;
    
    $assunto = "Confirmação de Agendamento - $nome_clube";
    
    $mensagem = "
    <html>
    <head>
        <title>Confirmação de Agendamento</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #1e3a8a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8fafc; }
            .footer { background: #334155; color: white; padding: 15px; text-align: center; }
            .button { background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>$nome_clube</h1>
                <h2>Confirmação de Agendamento</h2>
            </div>
            <div class='content'>
                <p>Olá <strong>$nome</strong>,</p>
                
                <p>Recebemos seu agendamento para teste na categoria <strong>" . strtoupper($categoria) . "</strong>.</p>
                
                <p><strong>Número do Agendamento:</strong> #$agendamento_id</p>
                
                <p>Nossa equipe entrará em contato em breve para confirmar a data e horário do seu teste.</p>
                
                <p><strong>O que levar no dia do teste:</strong></p>
                <ul>
                    <li>Documento de identidade</li>
                    <li>Atestado médico</li>
                    <li>Material esportivo (chuteira, uniforme)</li>
                    <li>Autorização dos pais (se menor de idade)</li>
                </ul>
                
                <p>Em caso de dúvidas, entre em contato conosco:</p>
                <p><strong>Telefone:</strong> (11) 4512-3456<br>
                <strong>Email:</strong> $email_admin</p>
                
                <p>Estamos ansiosos para conhecer seu talento!</p>
                
                <p>Atenciosamente,<br>
                <strong>Equipe $nome_clube</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; 2024 $nome_clube - Todos os direitos reservados</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        "From: $nome_clube <$email_admin>",
        "Reply-To: $email_admin",
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Enviar email
    mail($email_destinatario, $assunto, $mensagem, implode("\r\n", $headers));
    
    // Enviar cópia para administração
    $assunto_admin = "Novo Agendamento - $nome ($categoria)";
    $mensagem_admin = "
        Novo agendamento recebido:
        
        Nome: $nome
        Categoria: $categoria
        Email: $email_destinatario
        ID: #$agendamento_id
        
        Acesse o painel administrativo para mais detalhes.
    ";
    
    mail($email_admin, $assunto_admin, $mensagem_admin, "From: $email_admin");
}

// Se chegou até aqui sem POST, redirecionar
header('Location: index.html');
exit;
?>