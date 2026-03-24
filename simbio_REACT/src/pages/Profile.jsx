import React, { useState, useEffect } from 'react';

const Profile = ({ userEmail }) => {
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);

  const PROJECTS_API_URL = 'http://localhost/tu-proyecto/simbio_BACKEND/includes/user_projects.php';

  useEffect(() => {
    const fetchUserProjects = async () => {
      try {
        setLoading(true);
        const response = await fetch(
          `${PROJECTS_API_URL}?email=${encodeURIComponent(userEmail)}`
        );

        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        
        const data = await response.json();
        setProjects(data);
      } catch (error) {
        console.error('Error fetching user projects:', error);
      } finally {
        setLoading(false);
      }
    };

    if (userEmail) {
      fetchUserProjects();
    }
  }, [userEmail]);

  if (loading) return <p>Cargando proyectos...</p>;

  return (
    <div id="user-projects" className="projects-grid">
      {projects.length > 0 ? (
        projects.map((project) => (
          <div key={project.id} className="project-card">
            <h3 className="project-title">{project.title}</h3>
            <img 
              src={project.image} 
              alt={project.title} 
              className="project-image" 
            />

            <div className="project-buttons">
              <a 
                href={`http://localhost/tu-proyecto/simbio_BACKEND/preview_video.php?video=${encodeURIComponent(project.video)}`} 
                className="btn btn-preview"
              >
                Preview
              </a>
              <a 
                href={`http://localhost/tu-proyecto/simbio_BACKEND/edit_project.php?id=${encodeURIComponent(project.id)}`} 
                className="btn btn-edit"
              >
                Editar
              </a>
            </div>
          </div>
        ))
      ) : (
        <p>No se encontraron proyectos para este usuario.</p>
      )}
    </div>
  );
};

export default Profile;