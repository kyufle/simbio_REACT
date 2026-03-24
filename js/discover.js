const PROJECTS_JSON = 'includes/projects.php';
const BUFFER_SIZE = 5;

let allProjects = [];
let buffer = [];
let currentVisible = null;

// 🔴 CAMBIO IMPORTANTE: No borramos este Set al reiniciar. 
// Mantiene los likes de la sesión actual para la "2nda ronda".
const userLikedSession = new Set(); 

const container = document.getElementById('discover-container');

/* ============================================================
   CREAR CARD DE PROYECTO
============================================================ */
function createProjectCard(project) {
    const card = document.createElement('div');
    card.classList.add('project-card');
    // Guardar el id del proyecto en el DOM para fácil acceso
    card.dataset.projectId = project.id;

    // Estado Like
    const alreadyLiked = project.liked || userLikedSession.has(parseInt(project.id));

    // Generación HTML Botones
    const buttonsHTML = alreadyLiked 
        ? `
            <div class="liked-indicator">❤️ Ja t'ha agradat aquest projecte</div>
            <div class="buttons">
                <button class="next-btn">
                    Següent
                </button>
            </div>
          `
        : `
            <div class="buttons">
                <button class="nope-btn" aria-label="No m'interessa">No m'interessa</button>
                <button class="like-btn" aria-label="M'agrada">M'agrada</button>
                <button class="next-btn hidden">Següent</button>
            </div>
          `;

    // Lógica Dinámica: ¿Es Video o Imagen?
    let mediaHTML = '';
    if (project.video) {
        // Es un video
        mediaHTML = `
            <video autoplay muted loop playsinline poster="${project.image}">
                <source src="${project.video}?t=${Date.now()}" type="video/mp4">
            </video>
        `;
    } else {
        // Es una imagen (fallback si no hay video)
        mediaHTML = `
            <img src="${project.image}" alt="${project.title}" style="width:100%; height:100%; object-fit:cover;">
        `;
    }

    card.innerHTML = `
        <header>
            <h2>${project.title}</h2>
            <p><strong>${project.type}:</strong> ${project.entity}</p>
        </header>

        <section class="video-section">
            ${mediaHTML}
        </section>

        <section class="actions">
            ${buttonsHTML}
            <nav class="bottom-bar">
                <a href="profile.php">Perfil</a>
                <a href="chat.php?user_id=${project.user_id}">Conversa</a>
                <button class="toggle-details">Detalls</button>
            </nav>
        </section>

        <aside class="details hidden">
            <button class="close-details" aria-label="Tancar">&times;</button>
            <h3>Descripció</h3>
            <p class="description">${project.description}</p>
            <h3>Etiquetes</h3>
            <div class="tags">
            ${project.tags && Array.isArray(project.tags) ? project.tags.map(tag => `<span class="tag">${tag}</span>`).join('') : ''}
            </div>
        </aside>
    `;

    // Icono de Match
    if (project.match) {
        const matchIcon = document.createElement('div');
        matchIcon.className = 'match-icon';
        matchIcon.innerHTML = '💖 Possible match'; 
        card.appendChild(matchIcon);
    }

    // Event Listeners
    const openDetailsBtn = card.querySelector('.toggle-details');
    const closeDetailsBtn = card.querySelector('.close-details');
    const detailsDiv = card.querySelector('.details');
    const video = card.querySelector('video');

    if (openDetailsBtn && closeDetailsBtn && detailsDiv) {
        openDetailsBtn.addEventListener('click', () => {
            detailsDiv.classList.remove('hidden');
            if(video) video.pause();
        });

        closeDetailsBtn.addEventListener('click', () => {
            detailsDiv.classList.add('hidden');
            if(video) video.play();
        });
    }

    return card;
}

/* ============================================================
   SWIPE ANIMACIÓN
============================================================ */
function animateSwipe(card, direction) {
    if (!card) return;

    if (direction === "like") card.classList.add("swipe-right");
    if (direction === "nope") card.classList.add("swipe-left");

    card.addEventListener("animationend", () => {
        card.remove();
        showNextProject();
    }, { once: true });
}

/* ============================================================
   GESTIÓN DE ESTADO (LIKE/NOPE)
============================================================ */
function handleLikeAction(card) {
    const projectId = card.dataset.projectId;
    
    // Obtener el nombre del proyecto desde el header
    const projectTitle = card.querySelector('header h2')?.textContent || 'aquest projecte';
    
    // Guardar en sesión
    userLikedSession.add(parseInt(projectId));

    // Feedback visual inmediato (Transformar botones)
    const buttonsContainer = card.querySelector('.buttons');
    const actionsContainer = card.querySelector('.actions');
    
    if (buttonsContainer) {
        // Inyectar el indicador si no existe
        if (!card.querySelector('.liked-indicator')) {
            const indicator = document.createElement('div');
            indicator.className = 'liked-indicator';
            indicator.innerHTML = "❤️ Ja t'ha agradat aquest projecte";
            actionsContainer.insertBefore(indicator, buttonsContainer);
        }
        
        // Ocultar botones de decisión, mostrar siguiente
        const likeBtn = card.querySelector('.like-btn');
        const nopeBtn = card.querySelector('.nope-btn');
        const nextBtn = card.querySelector('.next-btn');
        
        if(likeBtn) likeBtn.classList.add('hidden');
        if(nopeBtn) nopeBtn.classList.add('hidden');
        if(nextBtn) nextBtn.classList.remove('hidden');
    }

    // ⭐ ÚNICO TOAST - Con nombre del proyecto
    fetch('includes/like_project.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_id: projectId })
    })
    .then(res => res.json())
    .then(data => {
        console.log("Respuesta like_project.php:", data); // 👈 para debug

        if (data.success) {
            if (typeof window.mostrarExito === 'function') {
                window.mostrarExito(
                    `❤️ Has dado like a "${projectTitle}"`,
                    `Ahora puedes <a href="chat.php?user_id=${data.owner_id}">iniciar una conversación</a> con ${data.owner_name}`,
                    {
                        actionText: "Ir a conversación",
                        actionCallback: () => {
                            window.location.href = `chat.php?user_id=${data.owner_id}`;
                        }
                    }
                );
            }
        } else {
            console.error('Error al dar like:', data.error || 'Unknown error');
        }
    })
    .catch(err => console.error('Fallo en fetch like_project.php:', err));

    // Animar salida
    animateSwipe(card, "like");
}

/* ============================================================
   REINICIAR FEED
============================================================ */
function restartFeed() {
    allProjects = [];
    buffer = [];
    currentVisible = null;

    container.innerHTML = '<div style="text-align:center; padding:20px;">Carregant de nou...</div>';

    // Volver a iniciar
    setTimeout(initDiscover, 500);
}

/* ============================================================
   EVENT DELEGATION (CLICKS)
============================================================ */
document.addEventListener("click", (e) => {
    if (!currentVisible) return;

    // Helper para loguear acción en el servidor
    function logUserAction(accion, projectId) {
        fetch('log_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ accion: accion, project_id: projectId })
        })
        .then(res => {
            if (!res.ok) throw new Error('Error al registrar log');
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                console.error('Error en log_action.php:', data.error);
            }
        })
        .catch(err => {
            console.error('Fallo al registrar acción en log:', err);
        });
    }

    // Obtener id de proyecto actual
    const cardProjectId = currentVisible && currentVisible.dataset && currentVisible.dataset.projectId ? currentVisible.dataset.projectId : null;

    // ⭐ BOTÓN LIKE - SIN TOAST AQUÍ (ya lo muestra handleLikeAction)
    if (e.target.classList.contains("like-btn") || e.target.closest(".like-btn")) {
        handleLikeAction(currentVisible);
        return;
    }

    // BOTÓN NOPE
    if (e.target.classList.contains("nope-btn") || e.target.closest(".nope-btn")) {
        if (cardProjectId) logUserAction("dislike", cardProjectId);
        animateSwipe(currentVisible, "nope");
        return;
    }

    // BOTÓN SIGUIENTE
    if (e.target.closest(".next-btn")) {
        animateSwipe(currentVisible, "like");
        return;
    }
});

// Bloquear recarga de página (F5, Ctrl+R, etc.)
window.addEventListener('beforeunload', function(e) {
    e.preventDefault();
    e.returnValue = '';
    return '';
});

/* ============================================================
   MOSTRAR SIGUIENTE PROYECTO
============================================================ */
function showNextProject() {
    container.innerHTML = '';

    // Si se acaba el buffer
    if (buffer.length === 0) {
        container.innerHTML = `
            <div class="empty-message">
                <div class="empty-icon">🎉</div>
                <h2>No hi ha més vídeos per mostrar</h2>
                <p>Has vist tots els vídeos disponibles.</p>
                <button id="restart-feed-btn" class="restart-btn">
                    🔄 Tornar a començar
                </button>
            </div>
        `;
        
        // Listener para el botón de reinicio
        const btn = document.getElementById('restart-feed-btn');
        if(btn) btn.addEventListener('click', restartFeed);
        
        return;
    }

    const project = buffer.shift();
    currentVisible = createProjectCard(project);
    container.appendChild(currentVisible);

    // Animación de entrada suave
    currentVisible.style.opacity = '0';
    currentVisible.style.transform = 'scale(0.95) translateY(20px)';
    
    // Forzar reflow para asegurar animación
    void currentVisible.offsetWidth; 
    
    currentVisible.style.transition = 'all 0.4s ease-out';
    currentVisible.style.opacity = '1';
    currentVisible.style.transform = 'scale(1) translateY(0)';

    // Precarga del siguiente video
    if (allProjects.length > 0) {
        const nextProject = allProjects.shift();
        buffer.push(nextProject);
        if (nextProject && nextProject.video) {
            const preload = document.createElement('video');
            preload.src = nextProject.video;
            preload.preload = 'metadata';
        }
    }

    // Reproducir video
    const video = currentVisible.querySelector('video');
    if(video) video.play().catch(err => console.log('Autoplay prevenido por navegador'));
}

/* ============================================================
   SWIPE TÁCTIL
============================================================ */
let touchStartX = 0;
let touchStartY = 0;

document.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
    touchStartY = e.changedTouches[0].screenY;
}, { passive: true });

document.addEventListener('touchend', (e) => {
    if (!currentVisible) return;
    const endX = e.changedTouches[0].screenX;
    const endY = e.changedTouches[0].screenY;
    const diffX = endX - touchStartX;
    const diffY = Math.abs(endY - touchStartY);

    // Swipe horizontal significativo
    if (Math.abs(diffX) > 80 && Math.abs(diffX) > diffY) {
        const alreadyLiked = currentVisible.querySelector('.liked-indicator');
        
        if (diffX > 0) { 
            // Swipe DERECHA
            if (!alreadyLiked) handleLikeAction(currentVisible);
            else animateSwipe(currentVisible, "like");
        } else {
            // Swipe IZQUIERDA
            if (!alreadyLiked) animateSwipe(currentVisible, "nope");
            else {
                // Si ya estaba likeado y hace swipe izquierda, lo pasamos igual
                animateSwipe(currentVisible, "nope");
            }
        }
    }
}, { passive: true });

/* ============================================================
   TECLADO
============================================================ */
document.addEventListener('keydown', (e) => {
    if (!currentVisible) return;
    
    const alreadyLiked = currentVisible.querySelector('.liked-indicator');

    if (e.key === 'ArrowLeft') {
        animateSwipe(currentVisible, "nope");
    }
    
    if (e.key === 'ArrowRight') {
        if (!alreadyLiked) handleLikeAction(currentVisible);
        else animateSwipe(currentVisible, "like");
    }
});

/* ============================================================
   INICIALIZAR DISCOVER
============================================================ */
function initDiscover() {
    fetch(PROJECTS_JSON)
        .then(res => {
            if (!res.ok) throw new Error('Error al carregar');
            return res.json();
        })
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `
                    <div class="empty-message">
                         <h2>📭 No hi ha projectes</h2>
                         <p>Torna-ho a intentar més tard.</p>
                    </div>`;
                return;
            }
            
            // ✅ ALGORITMO FEEDS: 
            // Forzamos el orden en JS para asegurar que MATCHES salgan primero.
            allProjects = data.sort((a, b) => {
                if (a.match && !b.match) return -1;
                if (!a.match && b.match) return 1;
                return 0;
            });
            
            // Llenar buffer inicial
            buffer = allProjects.splice(0, BUFFER_SIZE);
            showNextProject();
        })
        .catch(err => {
            container.innerHTML = `
                <div class="error-message">
                    <h2>Error de connexió</h2>
                    <p>${err.message}</p>
                </div>
            `;
            console.error(err);
        });
}

document.addEventListener('DOMContentLoaded', initDiscover);