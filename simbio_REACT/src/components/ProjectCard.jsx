import React, { useState, useEffect } from 'react';

const ProjectCard = ({ project, onNext, logAction, sessionLikes }) => {
    const [showDetails, setShowDetails] = useState(false);
    const [isLiked, setIsLiked] = useState(project.liked || sessionLikes.has(parseInt(project.id)));
    const [swipeClass, setSwipeClass] = useState('');

    // Manejar el LIKE
    const handleLike = async () => {
        sessionLikes.add(parseInt(project.id));
        setIsLiked(true);

        try {
            const res = await fetch('http://localhost:8080/includes/like_project.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ project_id: project.id })
            });
            const data = await res.json();
            
            if (data.success && window.mostrarExito) {
                window.mostrarExito(`❤️ Has donat like a "${project.title}"`);
            }
        } catch (err) {
            console.error("Fetch error:", err);
        }
        
        triggerSwipe('swipe-right');
    };

    // Manejar el NOPE (Dislike)
    const handleNope = () => {
        logAction('dislike', project.id);
        triggerSwipe('swipe-left');
    };

    // Animación y cambio de proyecto
    const triggerSwipe = (className) => {
        setSwipeClass(className);
        setTimeout(() => {
            onNext(); // Llama a la función del padre para cambiar el index
        }, 400); // Duración de la animación CSS
    };

    // Atajos de teclado
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'ArrowLeft') handleNope();
            if (e.key === 'ArrowRight') isLiked ? triggerSwipe('swipe-right') : handleLike();
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isLiked]);

    return (
        <div className={`project-card ${swipeClass}`}>
            {project.match && <div className="match-icon">💖 Possible match</div>}
            
            <header>
                <h2>{project.title}</h2>
                <p><strong>{project.type}:</strong> {project.entity}</p>
            </header>

            <section className="video-section">
                {project.video ? (
                    <video autoPlay muted loop playsInline poster={project.image}>
                        <source src={`${project.video}?t=${Date.now()}`} type="video/mp4" />
                    </video>
                ) : (
                    <img src={project.image} alt={project.title} />
                )}
            </section>

            <section className="actions">
                {isLiked ? (
                    <>
                        <div className="liked-indicator">❤️ Ja t'ha agradat aquest projecte</div>
                        <div className="buttons">
                            <button className="next-btn" onClick={() => triggerSwipe('swipe-right')}>Següent</button>
                        </div>
                    </>
                ) : (
                    <div className="buttons">
                        <button className="nope-btn" onClick={handleNope}>No m'interessa</button>
                        <button className="like-btn" onClick={handleLike}>M'agrada</button>
                    </div>
                )}

                <nav className="bottom-bar">
                    <a href={`/profile/${project.user_id}`}>Perfil</a>
                    <a href={`/chat/${project.user_id}`}>Conversa</a>
                    <button onClick={() => setShowDetails(true)}>Detalls</button>
                </nav>
            </section>

            {/* Modal de Detalles */}
            <aside className={`details ${showDetails ? '' : 'hidden'}`}>
                <button className="close-details" onClick={() => setShowDetails(false)}>&times;</button>
                <h3>Descripció</h3>
                <p className="description">{project.description}</p>
                <h3>Etiquetes</h3>
                <div className="tags">
                    {project.tags?.map((tag, i) => (
                        <span key={i} className="tag">{tag}</span>
                    ))}
                </div>
            </aside>
        </div>
    );
};

export default ProjectCard;