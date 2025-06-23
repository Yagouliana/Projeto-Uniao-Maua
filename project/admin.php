<?php
session_start();

// Configurações do banco de dados
$host = 'localhost';
$dbname = 'uniao_maua_futsal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro na conexão: ' . $e->getMessage());
}

// Verificar login (simplificado)
if (!isset($_SESSION['admin_logado'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        // Verificação simples (em produção, usar hash da senha)
        if ($email === 'admin@uniaomauafutsal.com.br' && $senha === 'admin123') {
            $_SESSION['admin_logado'] = true;
            $_SESSION['admin_nome'] = 'Administrador';
        } else {
            $erro_login = 'Email ou senha incorretos';
        }
    }
    
    if (!isset($_SESSION['admin_logado'])) {
        // Mostrar formulário de login
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login - Painel Administrativo</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #1e3a8a, #3b82f6); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .login-container { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 100%; max-width: 400px; }
                .login-container h1 { text-align: center; color: #1e3a8a; margin-bottom: 2rem; }
                .form-group { margin-bottom: 1rem; }
                .form-group label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: bold; }
                .form-group input { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 5px; font-size: 1rem; }
                .form-group input:focus { outline: none; border-color: #3b82f6; }
                .btn-login { width: 100%; background: #1e3a8a; color: white; padding: 12px; border: none; border-radius: 5px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.3s; }
                .btn-login:hover { background: #3b82f6; }
                .erro { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 5px; margin-bottom: 1rem; text-align: center; }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h1>União Mauá Futsal</h1>
                <h2 style="text-align: center; color: #666; margin-bottom: 2rem;">Painel Administrativo</h2>
                
                <?php if (isset($erro_login)): ?>
                    <div class="erro"><?php echo $erro_login; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha">Senha:</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn-login">Entrar</button>
                </form>
                
                <p style="text-align: center; margin-top: 1rem; color: #666; font-size: 0.9rem;">
                    Email: admin@uniaomauafutsal.com.br<br>
                    Senha: admin123
                </p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Confirmar agendamento
    if (isset($_POST['confirmar_agendamento'])) {
        $id = $_POST['agendamento_id'];
        $data_teste = $_POST['data_teste'];
        $observacoes = $_POST['observacoes'];
        
        $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'confirmado', data_teste = ?, observacoes = ? WHERE id = ?");
        $stmt->execute([$data_teste, $observacoes, $id]);
        
        $sucesso = "Agendamento confirmado com sucesso!";
    }
    
    // Aprovar candidato
    if (isset($_POST['aprovar_candidato'])) {
        $agendamento_id = $_POST['agendamento_id'];
        $numero_camisa = $_POST['numero_camisa'];
        $posicao = $_POST['posicao'];
        
        // Buscar dados do agendamento
        $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id = ?");
        $stmt->execute([$agendamento_id]);
        $agendamento = $stmt->fetch();
        
        if ($agendamento) {
            // Inserir como atleta
            $stmt = $pdo->prepare("INSERT INTO atletas (agendamento_id, nome, data_nascimento, categoria, telefone, email, responsavel, numero_camisa, posicao, data_ingresso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $agendamento_id,
                $agendamento['nome'],
                $agendamento['data_nascimento'],
                $agendamento['categoria'],
                $agendamento['telefone'],
                $agendamento['email'],
                $agendamento['responsavel'],
                $numero_camisa,
                $posicao
            ]);
            
            $sucesso = "Candidato aprovado e adicionado como atleta!";
        }
    }
}

// Buscar dados para o dashboard
$stmt = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'pendente'");
$agendamentos_pendentes = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM atletas WHERE status = 'ativo'");
$atletas_ativos = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'confirmado'");
$testes_confirmados = $stmt->fetchColumn();

// Buscar agendamentos recentes
$stmt = $pdo->query("SELECT * FROM agendamentos ORDER BY data_agendamento DESC LIMIT 10");
$agendamentos_recentes = $stmt->fetchAll();

// Buscar atletas por categoria
$stmt = $pdo->query("SELECT categoria, COUNT(*) as total FROM atletas WHERE status = 'ativo' GROUP BY categoria ORDER BY categoria");
$atletas_por_categoria = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - União Mauá Futsal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f8fafc; }
        
        .header { background: #1e3a8a; color: white; padding: 1rem 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .logout-btn { background: #dc2626; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; font-size: 0.9rem; }
        .logout-btn:hover { background: #b91c1c; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { color: #1e3a8a; font-size: 2rem; margin-bottom: 0.5rem; }
        .stat-card p { color: #666; font-weight: bold; }
        
        .section { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .section h2 { color: #1e3a8a; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
        
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .table th { background: #f8fafc; font-weight: bold; color: #1e3a8a; }
        .table tr:hover { background: #f8fafc; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .badge-pendente { background: #fef3c7; color: #92400e; }
        .badge-confirmado { background: #d1fae5; color: #065f46; }
        .badge-realizado { background: #dbeafe; color: #1e40af; }
        
        .btn { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.9rem; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn:hover { opacity: 0.9; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 10px; }
        .modal h3 { color: #1e3a8a; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px; }
        
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: black; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        
        @media (max-width: 768px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .table { font-size: 0.9rem; }
            .table th, .table td { padding: 8px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>União Mauá Futsal - Painel Administrativo</h1>
            <div class="user-info">
                <span>Bem-vindo, <?php echo $_SESSION['admin_nome']; ?></span>
                <a href="?logout=1" class="logout-btn">Sair</a>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if (isset($sucesso)): ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><?php echo $agendamentos_pendentes; ?></h3>
                <p>Agendamentos Pendentes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $testes_confirmados; ?></h3>
                <p>Testes Confirmados</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $atletas_ativos; ?></h3>
                <p>Atletas Ativos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($atletas_por_categoria); ?></h3>
                <p>Categorias Ativas</p>
            </div>
        </div>

        <!-- Agendamentos Recentes -->
        <div class="section">
            <h2>Agendamentos Recentes</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Email</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos_recentes as $agendamento): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($agendamento['nome']); ?></td>
                        <td><?php echo strtoupper($agendamento['categoria']); ?></td>
                        <td><?php echo htmlspecialchars($agendamento['email']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $agendamento['status']; ?>">
                                <?php echo ucfirst($agendamento['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($agendamento['status'] === 'pendente'): ?>
                                <button class="btn btn-primary" onclick="confirmarAgendamento(<?php echo $agendamento['id']; ?>, '<?php echo htmlspecialchars($agendamento['nome']); ?>')">Confirmar</button>
                            <?php elseif ($agendamento['status'] === 'confirmado'): ?>
                                <button class="btn btn-success" onclick="aprovarCandidato(<?php echo $agendamento['id']; ?>, '<?php echo htmlspecialchars($agendamento['nome']); ?>')">Aprovar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Atletas por Categoria -->
        <div class="section">
            <h2>Atletas por Categoria</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Total de Atletas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($atletas_por_categoria as $categoria): ?>
                    <tr>
                        <td><?php echo strtoupper($categoria['categoria']); ?></td>
                        <td><?php echo $categoria['total']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Confirmar Agendamento -->
    <div id="modalConfirmar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal('modalConfirmar')">&times;</span>
            <h3>Confirmar Agendamento</h3>
            <form method="POST">
                <input type="hidden" id="confirmar_agendamento_id" name="agendamento_id">
                <input type="hidden" name="confirmar_agendamento" value="1">
                
                <div class="form-group">
                    <label>Candidato:</label>
                    <input type="text" id="confirmar_nome" readonly>
                </div>
                
                <div class="form-group">
                    <label for="data_teste">Data e Hora do Teste:</label>
                    <input type="datetime-local" id="data_teste" name="data_teste" required>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="3" placeholder="Instruções para o candidato..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Confirmar Agendamento</button>
            </form>
        </div>
    </div>

    <!-- Modal Aprovar Candidato -->
    <div id="modalAprovar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal('modalAprovar')">&times;</span>
            <h3>Aprovar Candidato</h3>
            <form method="POST">
                <input type="hidden" id="aprovar_agendamento_id" name="agendamento_id">
                <input type="hidden" name="aprovar_candidato" value="1">
                
                <div class="form-group">
                    <label>Candidato:</label>
                    <input type="text" id="aprovar_nome" readonly>
                </div>
                
                <div class="form-group">
                    <label for="numero_camisa">Número da Camisa:</label>
                    <input type="number" id="numero_camisa" name="numero_camisa" min="1" max="99" required>
                </div>
                
                <div class="form-group">
                    <label for="posicao">Posição:</label>
                    <select id="posicao" name="posicao" required>
                        <option value="">Selecione a posição</option>
                        <option value="Goleiro">Goleiro</option>
                        <option value="Fixo">Fixo</option>
                        <option value="Ala">Ala</option>
                        <option value="Pivô">Pivô</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-success">Aprovar e Adicionar como Atleta</button>
            </form>
        </div>
    </div>

    <script>
        function confirmarAgendamento(id, nome) {
            document.getElementById('confirmar_agendamento_id').value = id;
            document.getElementById('confirmar_nome').value = nome;
            document.getElementById('modalConfirmar').style.display = 'block';
        }

        function aprovarCandidato(id, nome) {
            document.getElementById('aprovar_agendamento_id').value = id;
            document.getElementById('aprovar_nome').value = nome;
            document.getElementById('modalAprovar').style.display = 'block';
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Fechar modal clicando fora
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Definir data mínima para hoje
        document.getElementById('data_teste').min = new Date().toISOString().slice(0, 16);
    </script>
</body>
</html>