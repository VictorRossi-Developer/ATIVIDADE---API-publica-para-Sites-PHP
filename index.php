<?php
$resultado = null;
$erro = null;

if (isset($_GET['cep'])) {
    $cep = preg_replace('/[^0-9]/', '', $_GET['cep']);

    if (strlen($cep) === 8) {
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $contexto = stream_context_create([
            "http" => [
                "method" => "GET",
                "timeout" => 10
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ]);

        $response = @file_get_contents($url, false, $contexto);

        if ($response !== false) {
            $dados = json_decode($response, true);

            if (isset($dados['erro']) && $dados['erro'] === true) {
                $erro = "CEP não encontrado na base de dados do ViaCEP.";
            } else {
                $resultado = $dados;
            }
        } else {
            $erro = "Não foi possível conectar ao serviço do ViaCEP. Tente novamente mais tarde.";
        }
    } else {
        $erro = "Por favor, digite um CEP válido contendo 8 dígitos números.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de CEP - ViaCEP API</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main class="container">
        <header class="header">
            <h1>Consulta de Endereço via CEP</h1>
            <p>Digite o CEP para buscar as informações de logradouro, bairro, cidade e estado.</p>
        </header>

        <form action="index.php" method="GET" class="search-form">
            <div class="form-group">
                <label for="cep">CEP:</label>
                <input 
                    type="text" 
                    id="cep" 
                    name="cep" 
                    placeholder="Ex: 01001000 ou 01001-000" 
                    value="<?= isset($_GET['cep']) ? htmlspecialchars($_GET['cep']) : '' ?>"
                    required
                >
            </div>
            <button type="submit" class="btn-search">Consultar CEP</button>
        </form>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <p><?= htmlspecialchars($erro) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($resultado): ?>
            <section class="result-card">
                <h2>Resultado da Consulta</h2>
                <div class="result-details">
                    <p><strong>CEP:</strong> <?= htmlspecialchars($resultado['cep'] ?? 'N/A') ?></p>
                    <p><strong>Logradouro:</strong> <?= htmlspecialchars($resultado['logradouro'] ?? 'N/A') ?></p>
                    <p><strong>Bairro:</strong> <?= htmlspecialchars($resultado['bairro'] ?? 'N/A') ?></p>
                    <p><strong>Cidade:</strong> <?= htmlspecialchars($resultado['localidade'] ?? 'N/A') ?></p>
                    <p><strong>Estado:</strong> <?= htmlspecialchars($resultado['uf'] ?? 'N/A') ?></p>
                    <?php if (!empty($resultado['complemento'])): ?>
                        <p><strong>Complemento:</strong> <?= htmlspecialchars($resultado['complemento']) ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

</body>
</html>