<?php
require 'config.php';

$db  = db();
$id  = (int)($_GET['id'] ?? 0);
$editing = $id > 0;

$paciente = [];
$anamnese = [];
$campos   = $db->query("SELECT * FROM campos_anamnese WHERE ativo = 1 ORDER BY ordem ASC")->fetchAll();

if ($editing) {
    $stmt = $db->prepare("SELECT * FROM pacientes WHERE id = ?");
    $stmt->execute([$id]);
    $paciente = $stmt->fetch();
    if (!$paciente) { flash('Paciente não encontrado.', 'error'); redirect('pacientes.php'); }

    $stmt2 = $db->prepare("SELECT * FROM anamnese WHERE paciente_id = ? ORDER BY id DESC LIMIT 1");
    $stmt2->execute([$id]);
    $anamnese = $stmt2->fetch() ?: [];
}

$pageTitle  = $editing ? 'Editar Paciente' : 'Novo Paciente';
$activePage = $editing ? 'pacientes' : 'paciente_novo';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome         = trim($_POST['nome'] ?? '');
    $dataNasc     = $_POST['data_nascimento'] ?? '';
    $sexo         = $_POST['sexo'] ?? 'M';
    $cpf          = trim($_POST['cpf'] ?? '');
    $celular      = trim($_POST['celular'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $endereco     = trim($_POST['endereco'] ?? '');
    $cidade       = trim($_POST['cidade'] ?? '');
    $profissao    = trim($_POST['profissao'] ?? '');
    $estadoCivil  = trim($_POST['estado_civil'] ?? '');
    $observacoes  = trim($_POST['observacoes'] ?? '');

    if (!$nome) { flash('O nome do paciente é obrigatório.', 'error'); }
    else {
        if ($editing) {
            $stmt = $db->prepare(
                "UPDATE pacientes SET nome=?, data_nascimento=?, sexo=?, cpf=?, celular=?, email=?,
                 endereco=?, cidade=?, profissao=?, estado_civil=?, observacoes=? WHERE id=?"
            );
            $stmt->execute([$nome, $dataNasc ?: null, $sexo, $cpf, $celular, $email,
                            $endereco, $cidade, $profissao, $estadoCivil, $observacoes, $id]);
            $pacienteId = $id;
        } else {
            $stmt = $db->prepare(
                "INSERT INTO pacientes (nome, data_nascimento, sexo, cpf, celular, email,
                 endereco, cidade, profissao, estado_civil, observacoes) VALUES (?,?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->execute([$nome, $dataNasc ?: null, $sexo, $cpf, $celular, $email,
                            $endereco, $cidade, $profissao, $estadoCivil, $observacoes]);
            $pacienteId = $db->lastInsertId();
        }

        // Save anamnese
        $anamneseData = [];
        foreach ($campos as $campo) {
            $anamneseData[$campo['nome']] = trim($_POST['anamnese_' . $campo['nome']] ?? '');
        }

        // Check if anamnese exists
        $existsStmt = $db->prepare("SELECT id FROM anamnese WHERE paciente_id = ?");
        $existsStmt->execute([$pacienteId]);
        $existingAnamnese = $existsStmt->fetch();

        $cols = array_map(fn($c) => $c['nome'], $campos);
        $fixedCols = ['queixa_principal','historia_doenca','antecedentes_pessoais','antecedentes_familiares',
                      'habitos','alimentacao','sono','atividade_fisica','medicamentos','alergias',
                      'exame_fisico','hipotese_diagnostica','conduta'];

        if ($existingAnamnese) {
            $sets = implode(', ', array_map(fn($c) => "$c = ?", $fixedCols));
            $vals = array_map(fn($c) => $anamneseData[$c] ?? '', $fixedCols);
            $vals[] = $pacienteId;
            $db->prepare("UPDATE anamnese SET $sets WHERE paciente_id = ?")->execute($vals);
        } else {
            $colStr = implode(', ', $fixedCols);
            $phStr  = implode(', ', array_fill(0, count($fixedCols), '?'));
            $vals   = array_map(fn($c) => $anamneseData[$c] ?? '', $fixedCols);
            array_unshift($vals, $pacienteId);
            $db->prepare("INSERT INTO anamnese (paciente_id, $colStr) VALUES (?, $phStr)")->execute($vals);
        }

        flash($editing ? 'Paciente atualizado com sucesso.' : 'Paciente cadastrado com sucesso.');
        redirect('paciente_ver.php?id=' . $pacienteId);
    }
}

include 'includes/header.php';
?>

<div class="page-bar">
    <h2><?= $editing ? 'Editar Paciente' : 'Novo Paciente' ?></h2>
    <?php if ($editing): ?>
    <a href="paciente_ver.php?id=<?= $id ?>" class="btn btn-outline">← Voltar</a>
    <?php else: ?>
    <a href="pacientes.php" class="btn btn-outline">← Pacientes</a>
    <?php endif; ?>
</div>

<form method="post">
    <div class="card">
        <h2>📋 Dados Pessoais</h2>
        <div class="form-grid">
            <div class="form-group col-span-2">
                <label>Nome Completo *</label>
                <input type="text" name="nome" required value="<?= sanitize($paciente['nome'] ?? '') ?>" placeholder="Nome do paciente">
            </div>
            <div class="form-group">
                <label>Data de Nascimento</label>
                <input type="date" name="data_nascimento" value="<?= $paciente['data_nascimento'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>Sexo</label>
                <select name="sexo">
                    <option value="M" <?= ($paciente['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                    <option value="F" <?= ($paciente['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
                    <option value="O" <?= ($paciente['sexo'] ?? '') === 'O' ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>
            <div class="form-group">
                <label>CPF</label>
                <input type="text" name="cpf" value="<?= sanitize($paciente['cpf'] ?? '') ?>" placeholder="000.000.000-00">
            </div>
            <div class="form-group">
                <label>Celular</label>
                <input type="text" name="celular" value="<?= sanitize($paciente['celular'] ?? '') ?>" placeholder="(00) 00000-0000">
            </div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" value="<?= sanitize($paciente['email'] ?? '') ?>" placeholder="email@exemplo.com">
            </div>
            <div class="form-group">
                <label>Profissão</label>
                <input type="text" name="profissao" value="<?= sanitize($paciente['profissao'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Estado Civil</label>
                <select name="estado_civil">
                    <?php foreach (['','Solteiro(a)','Casado(a)','Divorciado(a)','Viúvo(a)','União Estável'] as $ec): ?>
                    <option value="<?= $ec ?>" <?= ($paciente['estado_civil'] ?? '') === $ec ? 'selected' : '' ?>><?= $ec ?: 'Selecione…' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-span-3">
                <label>Endereço</label>
                <input type="text" name="endereco" value="<?= sanitize($paciente['endereco'] ?? '') ?>" placeholder="Rua, número, bairro">
            </div>
            <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="cidade" value="<?= sanitize($paciente['cidade'] ?? '') ?>">
            </div>
            <div class="form-group col-span-2">
                <label>Observações</label>
                <textarea name="observacoes"><?= sanitize($paciente['observacoes'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>🌿 Anamnese</h2>
        <?php if (empty($campos)): ?>
        <p style="color:#9ca3af;font-size:.9rem;">Nenhum campo de anamnese configurado. Acesse <a href="configuracoes.php">Configurações</a> para adicionar.</p>
        <?php else: ?>
        <div class="form-grid form-grid-1">
            <?php foreach ($campos as $campo): ?>
            <div class="form-group">
                <label><?= sanitize($campo['label']) ?><?= $campo['obrigatorio'] ? ' *' : '' ?></label>
                <?php $val = sanitize($anamnese[$campo['nome']] ?? ''); ?>
                <?php if ($campo['tipo'] === 'textarea'): ?>
                <textarea name="anamnese_<?= $campo['nome'] ?>" <?= $campo['obrigatorio'] ? 'required' : '' ?>><?= $val ?></textarea>
                <?php elseif ($campo['tipo'] === 'select' && $campo['opcoes']): ?>
                <select name="anamnese_<?= $campo['nome'] ?>" <?= $campo['obrigatorio'] ? 'required' : '' ?>>
                    <option value="">Selecione…</option>
                    <?php foreach (explode("\n", $campo['opcoes']) as $opt): ?>
                    <?php $opt = trim($opt); if (!$opt) continue; ?>
                    <option value="<?= sanitize($opt) ?>" <?= ($anamnese[$campo['nome']] ?? '') === $opt ? 'selected' : '' ?>><?= sanitize($opt) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <input type="text" name="anamnese_<?= $campo['nome'] ?>" value="<?= $val ?>" <?= $campo['obrigatorio'] ? 'required' : '' ?>>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 Salvar Paciente</button>
        <a href="<?= $editing ? 'paciente_ver.php?id='.$id : 'pacientes.php' ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
