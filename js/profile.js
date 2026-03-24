const PROJECTS_API_URL = 'includes/user_projects.php';

// Función para obtener los proyectos del usuario por email
async function fetchUserProjects(userEmail) {
    try {
        const response = await fetch(
            `${PROJECTS_API_URL}?email=${encodeURIComponent(userEmail)}`
        );

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }

        return await response.json();
    } catch (error) {
        console.error('Error fetching user projects:', error);
        return [];
    }
}

// Función para mostrar los proyectos en el perfil del usuario
async function displayUserProjects(userEmail) {
    const projectsContainer = document.getElementById('user-projects');
    projectsContainer.innerHTML = '';
    
    const projects = await fetchUserProjects(userEmail);

    projects.forEach(project => {
        const card = document.createElement('div');
        card.classList.add('project-card');

        // Título del proyecto
        const title = document.createElement('h3');
        title.textContent = project.title;
        title.classList.add('project-title');
        card.appendChild(title);

        // Imagen centrada
        const image = document.createElement('img');
        image.src = project.image;
        image.alt = project.title;
        image.classList.add('project-image');
        card.appendChild(image);

        // Contenedor de botones
        const buttonsContainer = document.createElement('div');
        buttonsContainer.classList.add('project-buttons');

        // Botón Preview
        const previewBtn = document.createElement('a');
        previewBtn.href = `preview_video.php?video=${encodeURIComponent(project.video)}`;
        previewBtn.textContent = 'Preview';
        previewBtn.classList.add('btn', 'btn-preview');

        // Botón Edit
        const editBtn = document.createElement('a');
        editBtn.href = `edit_project.php?id=${encodeURIComponent(project.id)}`;
        editBtn.textContent = 'Editar';
        editBtn.classList.add('btn', 'btn-edit');

        // Agregar botones al contenedor
        buttonsContainer.appendChild(previewBtn);
        buttonsContainer.appendChild(editBtn);

        // Agregar botones a la tarjeta
        card.appendChild(buttonsContainer);

        // Agregar tarjeta al contenedor principal
        projectsContainer.appendChild(card);
    });

    console.log(projects);
}


// Suponiendo que ya tienes el email del usuario autenticado
displayUserProjects(userEmail);