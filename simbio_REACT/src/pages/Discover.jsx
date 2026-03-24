import React, { useState, useEffect, useCallback } from 'react';
import ProjectCard from './ProjectCard';
import './Discover.css';

const Discover = () => {
    const [projects, setProjects] = useState([]);
    const [currentIndex, setCurrentIndex] = useState(0);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [sessionLikes] = useState(new Set());

    const fetchProjects = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch('http://localhost:8080/includes/projects.php');
            if (!response.ok) throw new Error('Error al carregar projectes');
            const data = await response.json();
            const sorted = data.sort((a, b) => (a.match === b.match ? 0 : a.match ? -1 : 1));
            setProjects(sorted);
            setCurrentIndex(0);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchProjects();
    }, [fetchProjects]);

    const handleNext = () => {
        setCurrentIndex((prev) => prev + 1);
    };
    
    const logAction = async (action, projectId) => {
        try {
            await fetch('http://localhost:8080/log_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: action, project_id: projectId })
            });
        } catch (err) {
            console.error("Error logging action:", err);
        }
    };

    if (loading) return <div className="loading">Carregant projectes...</div>;
    if (error) return <div className="error">Error: {error}</div>;

    // Pantalla de "No hay más proyectos"
    if (currentIndex >= projects.length) {
        return (
            <div className="empty-message">
                <div className="empty-icon">🎉</div>
                <h2>No hi ha més vídeos per mostrar</h2>
                <button className="restart-btn" onClick={fetchProjects}>
                    🔄 Tornar a començar
                </button>
            </div>
        );
    }

    const currentProject = projects[currentIndex];

    return (
        <div id="discover-container">
            <ProjectCard 
                key={currentProject.id}
                project={currentProject}
                onNext={handleNext}
                logAction={logAction}
                sessionLikes={sessionLikes}
            />
        </div>
    );
};

export default Discover;