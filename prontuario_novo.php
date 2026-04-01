<?php
require 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db         = db();
$id         = (int)($_GET['id'] ?? 0);
$pacienteId = (int)($_GET['paciente_id'] ?? 0);
$editing    = $id > 0;
$registro   = [];

// 👇 usuário logado (IMPORTANTE)
$usuarioId = $_SESSION['usuario_id'] ?? 1;

if ($editing) {
    $stmt = $db->prepare("SELECT * FROM prontuario WHERE id = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch();

    if (!$registro) {
        flash('Registro não encontrado.', 'error');
        redirect('prontuarios.php');
    }

    $pacienteId = $registro['paciente_id'];
}

<<<<<<< HEAD
if (!$pacienteId) {
    flash('Paciente não informado.', 'error');
    redirect('pacientes.php');
}

// paciente
$stmtP = $db->prepare("SELECT id, nome FROM pacientes WHERE id = ?");
$stmtP->execute([$pacienteId]);
$paciente = $stmtP->fetch();

if (!$paciente) {
    flash('Paciente não encontrado.', 'error');
    redirect('pacientes.php');
=======
// Load all patients for optional selection dropdown
$todosPacientes = $db->query("SELECT id, nome FROM pacientes ORDER BY nome ASC")->fetchAll();

$paciente = null;
if ($pacienteId) {
    $stmtP = $db->prepare("SELECT id, nome FROM pacientes WHERE id = ?");
    $stmtP->execute([$pacienteId]);
    $paciente = $stmtP->fetch();
    if (!$paciente) { flash('Paciente não encontrado.', 'error'); redirect('pacientes.php'); }
>>>>>>> d984d2c106e1ab24c503d07bee46a083f2c620c3
}

$pageTitle  = $editing ? 'Editar Prontuário' : 'Novo Prontuário';
$activePage = 'prontuarios';

// SALVAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< HEAD

=======
    if (!$editing) {
        $pacienteId = (int)($_POST['paciente_id'] ?? 0);
    }
>>>>>>> d984d2c106e1ab24c503d07bee46a083f2c620c3
    $dataConsulta = $_POST['data_consulta'] ?? date('Y-m-d');
    $tipo         = $_POST['tipo'] ?? 'consulta';
    $evolucao     = trim($_POST['evolucao'] ?? '');
    $prescricao   = trim($_POST['prescricao'] ?? '');
    $exames       = trim($_POST['exames'] ?? '');

<<<<<<< HEAD
    if ($editing) {

        $stmt = $db->prepare("
            UPDATE prontuario 
            SET data_atendimento=?, tipo_atendimento=?, subjetivo=?, prescricao=?, retorno=?, usuario_id=? 
            WHERE id=?
        ");

        $stmt->execute([
            $dataConsulta,
            $tipo,
            $evolucao,
            $prescricao,
            $exames,
            $usuarioId,
            $id
        ]);

    } else {

        $stmt = $db->prepare("
            INSERT INTO prontuario 
            (paciente_id, data_atendimento, tipo_atendimento, subjetivo, prescricao, retorno, usuario_id) 
            VALUES (?,?,?,?,?,?,?)
        ");

        $stmt->execute([
            $pacienteId,
            $dataConsulta,
            $tipo,
            $evolucao,
            $prescricao,
            $exames,
            $usuarioId
        ]);
    }
=======
    if (!$pacienteId) {
        flash('Selecione um paciente.', 'error');
    } else {
        if ($editing) {
            $stmt = $db->prepare(
                "UPDATE prontuario SET data_consulta=?, tipo=?, evolucao=?, prescricao=?, exames=? WHERE id=?"
            );
            $stmt->execute([$dataConsulta, $tipo, $evolucao, $prescricao, $exames, $id]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO prontuario (paciente_id, data_consulta, tipo, evolucao, prescricao, exames) VALUES (?,?,?,?,?,?)"
            );
            $stmt->execute([$pacienteId, $dataConsulta, $tipo, $evolucao, $prescricao, $exames]);
        }
>>>>>>> d984d2c106e1ab24c503d07bee46a083f2c620c3

        flash($editing ? 'Prontuário atualizado.' : 'Registro adicionado ao prontuário.');
        redirect('paciente_ver.php?id=' . $pacienteId . '#tab-prontuario');
    }
}

include 'includes/header.php';
?>

<div class="page-bar">
    <h2><?= $editing ? 'Editar Registro' : 'Novo Registro no Prontuário' ?></h2>
    <?php if ($paciente): ?>
    <a href="paciente_ver.php?id=<?= $pacienteId ?>#tab-prontuario" class="btn btn-outline">← Voltar</a>
    <?php else: ?>
    <a href="prontuarios.php" class="btn btn-outline">← Prontuários</a>
    <?php endif; ?>
</div>

<?php if ($paciente): ?>
<p style="color:#6b7280;font-size:.9rem;margin-bottom:1rem;">
    Paciente: <strong><?= sanitize($paciente['nome']) ?></strong>
</p>
<?php endif; ?>

<form method="post">
    <?php if ($pacienteId || $editing): ?><input type="hidden" name="paciente_id" value="<?= $pacienteId ?>"><?php endif; ?>
    <div class="card">
        <h2>📋 Dados do Registro</h2>

        <div class="form-grid form-grid-2">
<<<<<<< HEAD

=======
            <?php if (!$paciente): ?>
            <div class="form-group col-span-2">
                <label>Paciente *</label>
                <select name="paciente_id" required>
                    <option value="">Selecione o paciente…</option>
                    <?php foreach ($todosPacientes as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= sanitize($p['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
>>>>>>> d984d2c106e1ab24c503d07bee46a083f2c620c3
            <div class="form-group">
                <label>Data da Consulta</label>
                <input type="date" name="data_consulta" required
                       value="<?= isset($registro['data_atendimento']) 
                            ? date('Y-m-d', strtotime($registro['data_atendimento'])) 
                            : date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo">
                    <?php foreach (['consulta','retorno','urgencia'] as $t): ?>
                        <option value="<?= $t ?>" 
                            <?= ($registro['tipo_atendimento'] ?? 'consulta') === $t ? 'selected' : '' ?>>
                            <?= ucfirst($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-span-2">
                <label>Evolução Clínica</label>
                <textarea name="evolucao"><?= sanitize($registro['subjetivo'] ?? '') ?></textarea>
            </div>

            <div class="form-group col-span-2">
                <label>Prescrição</label>
                <textarea name="prescricao"><?= sanitize($registro['prescricao'] ?? '') ?></textarea>
            </div>

            <div class="form-group col-span-2">
                <label>Exames / Retorno</label>
                <textarea name="exames"><?= sanitize($registro['retorno'] ?? '') ?></textarea>
            </div>

        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 Salvar</button>
        <?php if ($paciente): ?>
        <a href="paciente_ver.php?id=<?= $pacienteId ?>#tab-prontuario" class="btn btn-outline">Cancelar</a>
        <?php else: ?>
        <a href="prontuarios.php" class="btn btn-outline">Cancelar</a>
        <?php endif; ?>
    </div>
</form>

<?php include 'includes/footer.php'; ?>