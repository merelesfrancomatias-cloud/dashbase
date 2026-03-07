<?php
session_start();
require_once __DIR__ . '/_auth.php';
sa_check_auth();

$db = sa_db();

// ── Estadísticas principales ──────────────────────────────
$total_negocios  = $db->query("SELECT COUNT(*) FROM negocios")->fetchColumn();
$activos_hoy     = $db->query("SELECT COUNT(*) FROM negocios WHERE activo = 1")->fetchColumn();
$bloqueados      = $db->query("SELECT COUNT(*) FROM negocios WHERE bloqueado = 1")->fetchColumn();
$vencen_7dias    = $db->query("SELECT COUNT(*) FROM negocios WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND activo = 1")->fetchColumn();
$total_usuarios  = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

// Ingresos del mes actual
$ingresos_mes = $db->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE YEAR(fecha_pago)=YEAR(CURDATE()) AND MONTH(fecha_pago)=MONTH(CURDATE())")->fetchColumn();

// Negocios por plan
$por_plan = $db->query("
    SELECT p.nombre_display, COUNT(n.id) as total, p.color
    FROM negocios n
    LEFT JOIN planes p ON p.id = n.plan_id
    GROUP BY n.plan_id
    ORDER BY total DESC
")->fetchAll();

// Últimos 8 negocios creados
$ultimos_negocios = $db->query("
    SELECT n.id, n.nombre, n.email, n.activo, n.bloqueado,
           n.fecha_vencimiento, n.fecha_registro,
           p.nombre_display as plan_nombre, p.color as plan_color,
           r.nombre as rubro_nombre
    FROM negocios n
    LEFT JOIN planes p ON p.id = n.plan_id
    LEFT JOIN rubros r ON r.id = n.rubro_id
    ORDER BY n.fecha_registro DESC
    LIMIT 8
")->fetchAll();

// Negocios que vencen en los próximos 7 días
$proximos_vencimientos = $db->query("
    SELECT n.id, n.nombre, n.fecha_vencimiento, n.email,
           p.nombre_display as plan_nombre
    FROM negocios n
    LEFT JOIN planes p ON p.id = n.plan_id
    WHERE n.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
      AND n.activo = 1
    ORDER BY n.fecha_vencimiento ASC
    LIMIT 6
")->fetchAll();

// Últimos pagos
$ultimos_pagos = $db->query("
    SELECT pg.id, pg.monto, pg.fecha_pago, pg.metodo_pago,
           n.nombre as negocio_nombre,
           p.nombre_display as plan_nombre
    FROM pagos pg
    LEFT JOIN negocios n ON n.id = pg.negocio_id
    LEFT JOIN planes p   ON p.id = pg.plan_id
    ORDER BY pg.created_at DESC
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/_layout.php';
sa_layout_start('Dashboard', 'dashboard');
?>

<!-- ── Stats Grid ───────────────────────────────────────── -->
<div class="sa-stats-grid">
    <div class="sa-stat-card">
        <div class="sa-stat-icon green"><i class="fas fa-store"></i></div>
        <div class="sa-stat-body">
            <div class="value"><?= $total_negocios ?></div>
            <div class="label">Total Negocios</div>
            <div class="sub"><span class="up"><?= $activos_hoy ?> activos</span></div>
        </div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="sa-stat-body">
            <div class="value"><?= $total_usuarios ?></div>
            <div class="label">Usuarios Totales</div>
            <div class="sub">En todos los negocios</div>
        </div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-icon yellow"><i class="fas fa-clock"></i></div>
        <div class="sa-stat-body">
            <div class="value"><?= $vencen_7dias ?></div>
            <div class="label">Vencen en 7 días</div>
            <div class="sub">Requieren atención</div>
        </div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-icon red"><i class="fas fa-ban"></i></div>
        <div class="sa-stat-body">
            <div class="value"><?= $bloqueados ?></div>
            <div class="label">Bloqueados</div>
            <div class="sub">Sin acceso al sistema</div>
        </div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-icon purple"><i class="fas fa-dollar-sign"></i></div>
        <div class="sa-stat-body">
            <div class="value"><?= sa_format_money((float)$ingresos_mes) ?></div>
            <div class="label">Ingresos del Mes</div>
            <div class="sub"><?= date('F Y') ?></div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
    <!-- ── Negocios por plan ── -->
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><i class="fas fa-layer-group" style="color:var(--sa-primary);margin-right:8px"></i>Negocios por Plan</h3>
        </div>
        <div style="padding:20px">
            <?php if (empty($por_plan)): ?>
            <div class="sa-empty"><i class="fas fa-layer-group"></i><p>Sin datos</p></div>
            <?php else: ?>
            <?php foreach ($por_plan as $pp):
                $pct = $total_negocios > 0 ? round(($pp['total'] / $total_negocios) * 100) : 0;
                $color = $pp['color'] ?: '#0FD186';
            ?>
            <div style="margin-bottom:14px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                    <span style="font-size:13px;font-weight:600;color:var(--sa-text)"><?= htmlspecialchars($pp['nombre_display'] ?? 'Sin plan') ?></span>
                    <span style="font-size:12px;color:var(--sa-muted)"><?= $pp['total'] ?> negocios · <?= $pct ?>%</span>
                </div>
                <div class="progress-bar-wrap" style="width:100%;height:8px">
                    <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($color) ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Vencimientos próximos ── -->
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><i class="fas fa-calendar-exclamation" style="color:var(--sa-warning);margin-right:8px"></i>Vencen esta semana</h3>
            <a href="negocios/index.php?filtro=vencen" class="sa-btn ghost sm">Ver todos</a>
        </div>
        <?php if (empty($proximos_vencimientos)): ?>
        <div class="sa-empty"><i class="fas fa-calendar-check"></i><p>Ninguno vence esta semana 🎉</p></div>
        <?php else: ?>
        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead><tr><th>Negocio</th><th>Plan</th><th>Vence</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($proximos_vencimientos as $v):
                    $dias = sa_dias_restantes($v['fecha_vencimiento']);
                    $chipClass = $dias <= 2 ? 'exp' : ($dias <= 5 ? 'warn' : 'ok');
                ?>
                <tr>
                    <td style="font-weight:600"><?= htmlspecialchars($v['nombre']) ?></td>
                    <td><span class="sa-pill gray"><?= htmlspecialchars($v['plan_nombre'] ?? '—') ?></span></td>
                    <td><?= date('d/m/Y', strtotime($v['fecha_vencimiento'])) ?></td>
                    <td><span class="dias-chip <?= $chipClass ?>"><?= $dias ?>d</span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:24px">
    <!-- ── Últimos negocios ── -->
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><i class="fas fa-store" style="color:var(--sa-primary);margin-right:8px"></i>Últimos Negocios</h3>
            <a href="negocios/index.php" class="sa-btn ghost sm">Ver todos</a>
        </div>
        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead><tr><th>Negocio</th><th>Rubro</th><th>Plan</th><th>Estado</th><th>Vence</th></tr></thead>
                <tbody>
                <?php foreach ($ultimos_negocios as $neg):
                    $statusClass = $neg['bloqueado'] ? 'red' : ($neg['activo'] ? 'green' : 'gray');
                    $statusLabel = $neg['bloqueado'] ? 'Bloqueado' : ($neg['activo'] ? 'Activo' : 'Inactivo');
                    $dias = $neg['fecha_vencimiento'] ? sa_dias_restantes($neg['fecha_vencimiento']) : null;
                    $chipClass = is_null($dias) ? '' : ($dias < 0 ? 'exp' : ($dias <= 5 ? 'warn' : 'ok'));
                ?>
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--sa-text)"><?= htmlspecialchars($neg['nombre']) ?></div>
                        <div style="font-size:11px;color:var(--sa-muted)"><?= htmlspecialchars($neg['email'] ?? '') ?></div>
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px"><?= htmlspecialchars($neg['rubro_nombre'] ?? '—') ?></td>
                    <td>
                        <span class="sa-pill gray" style="<?= $neg['plan_color'] ? 'border-color:'.htmlspecialchars($neg['plan_color']).'40;color:'.htmlspecialchars($neg['plan_color']) : '' ?>">
                            <?= htmlspecialchars($neg['plan_nombre'] ?? '—') ?>
                        </span>
                    </td>
                    <td><span class="sa-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                    <td>
                        <?php if ($neg['fecha_vencimiento']): ?>
                        <span class="dias-chip <?= $chipClass ?>">
                            <?= $dias >= 0 ? $dias.'d' : 'Vencido' ?>
                        </span>
                        <?php else: ?>
                        <span style="color:var(--sa-muted)">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Últimos pagos ── -->
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><i class="fas fa-dollar-sign" style="color:var(--sa-purple);margin-right:8px"></i>Últimos Pagos</h3>
            <a href="pagos/index.php" class="sa-btn ghost sm">Ver todos</a>
        </div>
        <?php if (empty($ultimos_pagos)): ?>
        <div class="sa-empty"><i class="fas fa-receipt"></i><p>Sin pagos registrados</p></div>
        <?php else: ?>
        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead><tr><th>Negocio</th><th>Monto</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php foreach ($ultimos_pagos as $pg): ?>
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:12px"><?= htmlspecialchars($pg['negocio_nombre']) ?></div>
                        <div style="font-size:11px;color:var(--sa-muted)"><?= htmlspecialchars($pg['metodo_pago']) ?></div>
                    </td>
                    <td style="font-weight:700;color:var(--sa-primary)"><?= sa_format_money((float)$pg['monto']) ?></td>
                    <td style="font-size:11px;color:var(--sa-muted)"><?= date('d/m/Y', strtotime($pg['fecha_pago'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php sa_layout_end(); ?>
