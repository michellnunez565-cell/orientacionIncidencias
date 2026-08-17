<?php
// views/usuarios.php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/UserController.php';

$userController = new UserController();
$users = $userController->getAllUsers();

$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';
?>

<div class="card-header" style="margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary);">👥 Gestión de Usuarios y Maestros</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Cree y administre cuentas de acceso para los docentes del colegio.
        </p>
    </div>
    <button onclick="openModal('modalAddUser')" class="btn btn-primary">
        ➕ Registrar Nuevo Maestro / Usuario
    </button>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre Completo</th>
                    <th>Rol</th>
                    <th>Correo Electrónico</th>
                    <th>Teléfono</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                        <td>
                            <?php if ($u['role'] === 'orientadora'): ?>
                                <span class="badge badge-compromiso">Orientadora</span>
                            <?php else: ?>
                                <span class="badge badge-atendido">Docente / Maestro</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="index.php?action=eliminar_usuario&id=<?= $u['id'] ?>" class="btn btn-outline btn-sm" style="color: var(--danger);" onclick="return confirm('¿Eliminar esta cuenta de usuario?')">
                                    Eliminar
                                </a>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">(Mi Cuenta)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: REGISTRAR NUEVO MAESTRO / USUARIO -->
<div class="modal-overlay" id="modalAddUser">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">➕ Crear Cuenta de Maestro / Usuario</div>
            <button class="modal-close" onclick="closeModal('modalAddUser')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="index.php?action=guardar_usuario" method="POST">
                <div class="form-group">
                    <label for="u_fullname">Nombre Completo del Maestro(a) *</label>
                    <input type="text" id="u_fullname" name="full_name" class="form-control" placeholder="Ej. Prof. Juan Carlos Rodriguez" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="u_username">Nombre de Usuario *</label>
                        <input type="text" id="u_username" name="username" class="form-control" placeholder="Ej. proferodriguez" required>
                    </div>

                    <div class="form-group">
                        <label for="u_password">Contraseña *</label>
                        <input type="password" id="u_password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="u_role">Rol de Sistema *</label>
                    <select id="u_role" name="role" class="form-control" required>
                        <option value="docente">Docente / Maestro (Emisión de Reportes)</option>
                        <option value="orientadora">Orientadora (Acceso Total Administración)</option>
                    </select>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="u_email">Correo Electrónico</label>
                        <input type="email" id="u_email" name="email" class="form-control" placeholder="profesor@colegio.edu.hn">
                    </div>
                    <div class="form-group">
                        <label for="u_phone">Teléfono</label>
                        <input type="text" id="u_phone" name="phone" class="form-control" placeholder="Ej. 9988-1122">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('modalAddUser')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
