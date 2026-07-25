<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Contacto';
$pageDescription = 'Envía una consulta a Librería Horizonte mediante nuestro formulario de contacto.';
$errors = [];
$values = [
    'nombre' => '',
    'correo' => '',
    'asunto' => '',
    'comentario' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($values) as $field) {
        $values[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    $honeypot = trim((string) ($_POST['empresa'] ?? ''));

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'La sesión del formulario venció. Actualiza la página e inténtalo de nuevo.';
    }

    if ($values['nombre'] === '' || text_length($values['nombre']) < 2) {
        $errors['nombre'] = 'Escribe un nombre de al menos 2 caracteres.';
    } elseif (text_length($values['nombre']) > 100) {
        $errors['nombre'] = 'El nombre no puede superar 100 caracteres.';
    }

    if (!filter_var($values['correo'], FILTER_VALIDATE_EMAIL)) {
        $errors['correo'] = 'Escribe una dirección de correo válida.';
    } elseif (text_length($values['correo']) > 254) {
        $errors['correo'] = 'El correo no puede superar 254 caracteres.';
    }

    if ($values['asunto'] === '' || text_length($values['asunto']) < 3) {
        $errors['asunto'] = 'Escribe un asunto de al menos 3 caracteres.';
    } elseif (text_length($values['asunto']) > 150) {
        $errors['asunto'] = 'El asunto no puede superar 150 caracteres.';
    }

    if ($values['comentario'] === '' || text_length($values['comentario']) < 10) {
        $errors['comentario'] = 'El comentario debe contener al menos 10 caracteres.';
    } elseif (text_length($values['comentario']) > 2000) {
        $errors['comentario'] = 'El comentario no puede superar 2,000 caracteres.';
    }

    // El campo invisible ayuda a descartar envíos automáticos sin revelar el mecanismo.
    if ($honeypot !== '') {
        flash('success', 'Gracias. Recibimos tu mensaje correctamente.');
        header('Location: contacto.php?enviado=1', true, 303);
        exit;
    }

    if (!$errors) {
        $pdo = safe_db();

        if (!$pdo) {
            $errors['general'] = 'No pudimos guardar tu mensaje ahora. Inténtalo nuevamente en unos minutos.';
        } else {
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO contacto (fecha, correo, nombre, asunto, comentario)
                    VALUES (NOW(), :correo, :nombre, :asunto, :comentario)'
                );
                $statement->execute([
                    'correo' => $values['correo'],
                    'nombre' => $values['nombre'],
                    'asunto' => $values['asunto'],
                    'comentario' => $values['comentario'],
                ]);

                flash('success', '¡Mensaje enviado! Gracias por escribirnos; responderemos muy pronto.');
                unset($_SESSION['csrf_token']);
                header('Location: contacto.php?enviado=1', true, 303);
                exit;
            } catch (Throwable $exception) {
                error_log('No fue posible guardar el contacto: ' . $exception->getMessage());
                $errors['general'] = 'No pudimos guardar tu mensaje ahora. Inténtalo nuevamente en unos minutos.';
            }
        }
    }
}

$flashMessage = consume_flash();

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero--contact">
    <div class="container page-hero__inner">
        <div data-reveal>
            <p class="eyebrow">Estamos para ayudarte</p>
            <h1>Hablemos de libros, ideas y próximas lecturas.</h1>
            <p>Completa el formulario y guardaremos tu consulta para atenderla lo antes posible.</p>
        </div>
        <div class="page-hero__aside page-hero__aside--text" aria-hidden="true">
            <span>24 h</span>
            <small>respuesta estimada</small>
        </div>
    </div>
</section>

<section class="contact-section">
    <div class="container contact-grid">
        <aside class="contact-info" data-reveal>
            <p class="eyebrow">Tu mensaje importa</p>
            <h2>Cuéntanos cómo podemos orientarte.</h2>
            <p>
                Puedes consultarnos sobre títulos, autores o cualquier información del catálogo.
                Los campos marcados son obligatorios.
            </p>
            <div class="contact-info__items">
                <div>
                    <span aria-hidden="true">01</span>
                    <p><strong>Describe tu consulta</strong><br>Mientras más contexto compartas, mejor podremos ayudarte.</p>
                </div>
                <div>
                    <span aria-hidden="true">02</span>
                    <p><strong>Guardamos tu mensaje</strong><br>La información se registra de forma segura en nuestra base de datos.</p>
                </div>
                <div>
                    <span aria-hidden="true">03</span>
                    <p><strong>Te responderemos</strong><br>Usaremos el correo indicado únicamente para atender tu solicitud.</p>
                </div>
            </div>
        </aside>

        <div class="contact-form-card" data-reveal>
            <?php if ($flashMessage): ?>
                <div class="alert alert--<?= e($flashMessage['type']) ?>" role="status" data-alert>
                    <p><?= e($flashMessage['message']) ?></p>
                    <button type="button" aria-label="Cerrar mensaje" data-alert-close>×</button>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert--error" role="alert">
                    <p><?= e($errors['general']) ?></p>
                </div>
            <?php endif; ?>

            <form class="contact-form" action="contacto.php" method="post" novalidate data-contact-form>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="honeypot" aria-hidden="true">
                    <label for="empresa">Empresa</label>
                    <input id="empresa" name="empresa" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="nombre">Nombre completo <span aria-hidden="true">*</span></label>
                        <input
                            id="nombre"
                            name="nombre"
                            type="text"
                            value="<?= e($values['nombre']) ?>"
                            maxlength="100"
                            autocomplete="name"
                            required
                            aria-describedby="<?= isset($errors['nombre']) ? 'error-nombre' : '' ?>"
                            <?= isset($errors['nombre']) ? 'aria-invalid="true"' : '' ?>
                        >
                        <?php if (isset($errors['nombre'])): ?>
                            <small class="field__error" id="error-nombre"><?= e($errors['nombre']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="correo">Correo electrónico <span aria-hidden="true">*</span></label>
                        <input
                            id="correo"
                            name="correo"
                            type="email"
                            value="<?= e($values['correo']) ?>"
                            maxlength="254"
                            autocomplete="email"
                            required
                            aria-describedby="<?= isset($errors['correo']) ? 'error-correo' : '' ?>"
                            <?= isset($errors['correo']) ? 'aria-invalid="true"' : '' ?>
                        >
                        <?php if (isset($errors['correo'])): ?>
                            <small class="field__error" id="error-correo"><?= e($errors['correo']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="asunto">Asunto <span aria-hidden="true">*</span></label>
                    <input
                        id="asunto"
                        name="asunto"
                        type="text"
                        value="<?= e($values['asunto']) ?>"
                        maxlength="150"
                        required
                        aria-describedby="<?= isset($errors['asunto']) ? 'error-asunto' : '' ?>"
                        <?= isset($errors['asunto']) ? 'aria-invalid="true"' : '' ?>
                    >
                    <?php if (isset($errors['asunto'])): ?>
                        <small class="field__error" id="error-asunto"><?= e($errors['asunto']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <div class="field__label-row">
                        <label for="comentario">Comentario <span aria-hidden="true">*</span></label>
                        <small><span data-character-count>0</span>/2000</small>
                    </div>
                    <textarea
                        id="comentario"
                        name="comentario"
                        rows="7"
                        minlength="10"
                        maxlength="2000"
                        required
                        aria-describedby="<?= isset($errors['comentario']) ? 'error-comentario' : 'comentario-ayuda' ?>"
                        <?= isset($errors['comentario']) ? 'aria-invalid="true"' : '' ?>
                    ><?= e($values['comentario']) ?></textarea>
                    <?php if (isset($errors['comentario'])): ?>
                        <small class="field__error" id="error-comentario"><?= e($errors['comentario']) ?></small>
                    <?php else: ?>
                        <small id="comentario-ayuda">Incluye al menos 10 caracteres.</small>
                    <?php endif; ?>
                </div>

                <div class="contact-form__footer">
                    <p>Al enviar aceptas que guardemos estos datos para responder tu consulta.</p>
                    <button class="button button--primary" type="submit">Enviar mensaje</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
