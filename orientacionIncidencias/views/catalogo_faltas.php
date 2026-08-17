<?php
// views/catalogo_faltas.php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/IncidentController.php';

$incidentController = new IncidentController();
$incidents = $incidentController->getAllIncidentTypes(false); // Obtener todos (activos e inactivos)

$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';
?>

<div class="card-header" style="margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary);">⚙️ Catálogo y Clasificación de Incidencias</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Administre la lista de faltas disciplinarias y agregue nuevas faltas personalizadas al sistema.
        </p>
    </div>
    <!-- BOTÓN DESTACADO PARA AGREGAR MÁS FALTAS -->
    <button onclick="openModal('modalAddIncident')" class="btn btn-secondary" style="padding: 0.75rem 1.25rem; font-size: 1rem; box-shadow: 0 4px 6px rgba(217, 119, 6, 0.2);">
        ➕ Agregar Nueva Incidencia
    </button>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Faltas Registradas en el Catálogo</div>
        <input type="text" class="form-control live-search-input" data-table="table-catalog" placeholder="🔎 Buscar incidencia..." style="max-width: 280px;">
    </div>

    <div class="table-responsive">
        <table class="table" id="table-catalog">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre de la Falta / Incidencia</th>
                    <th>Categoría</th>
                    <th>Clasificación (Gravedad)</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidents as $inc): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($inc['code']) ?></code></td>
                        <td><strong><?= htmlspecialchars($inc['name']) ?></strong></td>
                        <td><?= htmlspecialchars($inc['category']) ?></td>
                        <td>
                            <span class="badge badge-<?= strtolower(str_replace(' ', '-', $inc['severity'])) ?>">
                                <?= htmlspecialchars($inc['severity']) ?>
                            </span>
                        </td>
                        <td style="font-size: 0.85rem; max-width: 300px; color: var(--text-muted);">
                            <?= htmlspecialchars($inc['description'] ?? 'Sin descripción') ?>
                        </td>
                        <td>
                            <?php if ($inc['is_active']): ?>
                                <span class="badge badge-resuelto">Activa</span>
                            <?php else: ?>
                                <span class="badge badge-sancion">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($inc['is_active']): ?>
                                <a href="index.php?action=eliminar_incidencia_tipo&id=<?= $inc['id'] ?>" class="btn btn-outline btn-sm" style="color: var(--danger);" onclick="return confirm('¿Desactivar esta falta del catálogo?')">
                                    Desactivar
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: AGREGAR NUEVA INCIDENCIA / FALTA AL CATÁLOGO -->
<div class="modal-overlay" id="modalAddIncident">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">➕ Agregar Nueva Incidencia al Catálogo</div>
            <button class="modal-close" onclick="closeModal('modalAddIncident')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="index.php?action=guardar_incidencia_tipo" method="POST">
                <div class="form-group">
                    <label for="inc_name">Nombre de la Falta o Incidencia *</label>
                    <input type="text" id="inc_name" name="name" class="form-control" placeholder="Ej. Uso no autorizado de teléfono celular" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="inc_category">Categoría</label>
                        <select id="inc_category" name="category" class="form-control">
                            <option value="Conductual">Conductual</option>
                            <option value="Académica">Académica</option>
                            <option value="Respeto y Convivencia">Respeto y Convivencia</option>
                            <option value="Cuidado Institucional">Cuidado Institucional</option>
                            <option value="Asistencia y Puntualidad">Asistencia y Puntualidad</option>
                            <option value="Seguridad">Seguridad</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="inc_severity">Clasificación de Gravedad *</label>
                        <select id="inc_severity" name="severity" class="form-control" required>
                            <option value="Leve">Falta Leve</option>
                            <option value="Grave">Falta Grave</option>
                            <option value="Muy Grave">Falta Muy Grave</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inc_description">Descripción o Criterio de Aplicación</label>
                    <textarea id="inc_description" name="description" class="form-control" rows="3" placeholder="Explicación breve de cuándo se debe aplicar esta falta..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('modalAddIncident')">Cancelar</button>
                    <button type="submit" class="btn btn-secondary">💾 Guardar Incidencia en Catálogo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
