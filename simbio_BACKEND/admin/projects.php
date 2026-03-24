<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

// Comprobar que el admin está logueado
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Gestió de Projectes</title>
    <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>">
</head>
<body class="login-page-admin">
    <header>
        <div class="header-left">
            <a href="index.php" class="nav-link">Inici</a>
            <a href="users.php" class="nav-link">Usuaris</a>
            <a href="projects.php" class="nav-link">Projectes</a>
            <a href="settings.php" class="nav-link">Configuració</a>
        </div>
        <div class="header-right">
            <a href="https://youtu.be/zSXbPNl1RJw" target="_blank" class="btn-logout">Video</a>
            |
            <?php if (isAdminLoggedIn()): ?>
                <a href="logout.php" class="btn-logout">Tancar sessió</a>
            <?php else: ?>
                <a href="login.php">Iniciar sessió</a>
            <?php endif; ?>
        </div>
    </header>
<main>
    <h1>Gestió de Projectes</h1>
    <div id="user-projects"></div>
</main>

<script>
async function loadProjects() {
    try {
        const res = await fetch('projects_json.php'); // Endpoint que devuelve JSON de proyectos
        const projects = await res.json();
        const container = document.getElementById('user-projects');
        container.innerHTML = '';

        if(projects.length === 0){
            container.innerHTML = '<p style="text-align:center; color:#394867;">No hi ha projectes registrats.</p>';
            return;
        }

        projects.forEach(project => {
            const card = document.createElement('div');
            card.className = 'project-card';

            card.innerHTML = `
                <div class="project-title">${project.title}</div>
                <div class="media-wrapper" style="position:relative; width:100%; height:auto;">
                    <img src="${project.image}" class="project-image" id="img${project.id}" style="display:block; width:100%; height:auto; cursor:pointer;">
                    ${project.video ? `<video id="video${project.id}" controls style="display:none; width:100%; height:auto;"></video>` : ''}
                </div>
                <div class="project-buttons">
                    ${project.deleted
                        ? `<a href="#" class="btn btn-restore" onclick="toggleProjectStatus(${project.id}, 'restore'); return false;">Recuperar</a>`
                        : `<a href="#" class="btn btn-delete" onclick="toggleProjectStatus(${project.id}, 'delete'); return false;">Eliminar</a>`
                    }
                    ${project.video ? `<a href="javascript:void(0)" class="btn btn-preview" onclick="toggleVideo('video${project.id}', 'img${project.id}', '${project.video}')">Preview</a>` : ''}
                </div>
            `;


            container.appendChild(card);
        });

    } catch (e) {
        console.error("Error cargando proyectos:", e);
        document.getElementById('user-projects').innerHTML = '<p style="text-align:center; color:#FF3B3B;">Error cargando proyectos.</p>';
    }
}

async function toggleProjectStatus(projectId, action) {
    const actionText = action === 'delete' ? 'eliminar' : 'recuperar';
    
    if (action === 'delete' && !confirm('Segur que vols eliminar aquest projecte?')) {
        return;
    }
    
    try {
        const res = await fetch('toggle_project_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                project_id: projectId,
                action: action
            })
        });
        
        const data = await res.json();
        
        if (data.success) {
            alert(data.message);
            // Recargar los proyectos
            loadProjects();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (e) {
        console.error('Error:', e);
        alert('Error al ' + actionText + ' el projecte');
    }
}
function toggleVideo(videoId, imgId, videoSrc) {
    const video = document.getElementById(videoId);
    const img = document.getElementById(imgId);

    if (video.style.display === 'block') {
        // Ocultar video y mostrar imagen
        video.pause();
        video.style.display = 'none';
        img.style.display = 'block';
    } else {
        // Mostrar video y ocultar imagen
        const sourceElement = video.querySelector('source');
        if(!sourceElement || sourceElement.src !== videoSrc){
            video.innerHTML = `<source src="${videoSrc}" type="video/mp4">`;
            video.load();
        }
        img.style.display = 'none';
        video.style.display = 'block';
        video.play();
    }
}

// Cargar los proyectos al iniciar
loadProjects();
</script>
</body>
</html>