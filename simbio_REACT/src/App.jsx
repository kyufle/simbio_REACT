import React, { useState, useEffect, useCallback } from 'react';
import { BrowserRouter, Routes, Route, Link, useNavigate, useParams } from 'react-router-dom';

import './styles.css';
import './chat.css';
import './conversations.css';

import projectsData from './mockData.json';
import Login from './pages/Login';
import Header from './components/Header';
import MessagesList from './pages/MessagesList';
import useBodyClass from './hooks/useBodyClass';


function Discover() {
  useBodyClass('discover-page');
  const [projects, setProjects] = useState([]);
  const [animating, setAnimating] = useState(null);
  const [detailsOpen, setDetailsOpen] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [currentIndex, setCurrentIndex] = useState(0);

    const fetchProjects = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch('http://localhost:8080/includes/projects.php', {
                credentials: 'include',
            });
            if (!response.ok) throw new Error('Error al carregar projectes');
            const data = await response.json();
            const sorted = data.sort((a, b) => (a.match === b.match ? 0 : a.match ? -1 : 1));
            setProjects(sorted);
            setCurrentIndex(0);
        } catch (err) {
            // setError(err.message);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchProjects();
    }, [fetchProjects]);

    const currentProject = projects[currentIndex];
      const handleNext = () => {
        setCurrentIndex((prev) => prev + 1);
    };

  const handleAction = (type) => {
    if (!currentProject || animating) return;
    setAnimating(type);
    setTimeout(() => {
      handleNext();
      // setProjects(prev => prev.slice(1));
      setAnimating(null);
      setDetailsOpen(false);
    }, 500);
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
    

  return (
    <>
      <Header />
      <main id="discover-container">
        {projects.length === 0 ? (
          <div className="empty-message" style={{textAlign: 'center', padding: '20px'}}>
            <div className="empty-icon" style={{fontSize: '3rem'}}>🎉</div>
            <h2>No hi ha més vídeos per mostrar</h2>
            <p>Has vist tots els vídeos disponibles.</p>
            <button className="next-btn" style={{marginTop: '20px'}} onClick={() => setProjects(projectsData)}>
                🔄 Tornar a començar
            </button>
          </div>
        ) : (
          <div className={`project-card ${animating === 'like' ? 'swipe-right' : animating === 'nope' ? 'swipe-left' : ''}`}>
            <header>
                <h2>{currentProject.title}</h2>
                <p><strong>{currentProject.type}:</strong> {currentProject.entity}</p>
            </header>

            <section className="video-section">
                {currentProject.video ? (
                  <video autoPlay muted loop playsInline poster={currentProject.image}>
                      <source src={currentProject.video} type="video/mp4" />
                  </video>
                ) : (
                  <img src={currentProject.image} alt={currentProject.title} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                )}
            </section>

            <section className="actions">
                <div className="buttons">
                    <button className="nope-btn" title="No m'interessa" onClick={() => handleAction('nope')}>No m'interessa</button>
                    <button className="like-btn" title="M'agrada" onClick={() => handleAction('like')}>M'agrada</button>
                </div>
                <nav className="bottom-bar">
                    <Link to="/profile">Perfil</Link>
                    <Link to={`/chat/${currentProject.user_id}`}>Conversa</Link>
                    <button className="toggle-details" onClick={() => setDetailsOpen(true)}>Detalls</button>
                </nav>
            </section>

            <aside className={`details ${detailsOpen ? '' : 'hidden'}`}>
                <button className="close-details" onClick={() => setDetailsOpen(false)}>&times;</button>
                <h3>Descripció</h3>
                <p className="description">{currentProject.description}</p>
                <h3>Etiquetes</h3>
                <div className="tags">
                  {currentProject.tags && currentProject.tags.map(tag => (
                    <span key={tag} className="tag">{tag}</span>
                  ))}
                </div>
            </aside>
          </div>
        )}
      </main>
    </>
  );
}

// ------------------------------------
// Conversations Page
// ------------------------------------


// ------------------------------------
// Single Chat Page
// ------------------------------------
function Chat() {
  useBodyClass(''); // clear body class to not mess with chat layout which handles its own gradients
  const { id } = useParams();
  const [messages, setMessages] = useState([
    { from: 'her', text: 'Hola! Ens interessa molt el vostre perfil. Voldríem establir una conversa amb vosaltres sobre el nou projecte.' },
    { from: 'me', text: 'Moltes gràcies! Quan podríem tenir una reunió inicial?' }
  ]);
  const [input, setInput] = useState('');

  const handleSend = (e) => {
    e.preventDefault();
    if(!input.trim()) return;
    setMessages([...messages, { from: 'me', text: input }]);
    setInput('');
  };

  return (
    <div className="chat-container">
        <div className="chat-box">
            <header className="chat-header">
                <div className="chat-header-info">
                    <div className="chat-avatar">
                        {id === "32" ? <img src="/uploads/imageproject4.jpg" alt="g" /> : <div className="avatar-placeholder">EP</div>}
                    </div>
                    <div className="chat-header-text">
                        <h2>Xat del projecte</h2>
                        <p className="chat-entity">Empresa {id}</p>
                    </div>
                </div>
                <Link to="/messages" className="btn-back">Enrere</Link>
            </header>

            <div className="messages-container" id="messagesContainer">
                {messages.map((msg, i) => (
                  <div key={i} className={`message-bubble ${msg.from === 'me' ? 'sent' : 'received'}`}>
                      <div className="message-content">{msg.text}</div>
                      <div className="message-time">12:30</div>
                  </div>
                ))}
            </div>

            <div className="chat-input-area">
                <form className="message-form" onSubmit={handleSend}>
                    <input 
                        type="text" 
                        value={input}
                        onChange={e => setInput(e.target.value)}
                        className="message-input" 
                        placeholder="Escriu el teu missatge aquí..." 
                        autoComplete="off" 
                        maxLength="1000"
                    />
                    <button type="submit" className="btn-send">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
  );
}

// ------------------------------------
// Profile Page
// ------------------------------------
function Profile() {
  useBodyClass('login-page');
  return (
    <>
      <Header />
      <main style={{ marginTop: '30px', maxWidth: '500px' }}>
          <h1>Editar Perfil</h1>
          <form style={{display: 'flex', flexDirection: 'column', gap: '15px'}}>
              <label>
                  Nom:
                  <input type="text" defaultValue="Usuario" />
              </label>
              <label>
                  Cognoms:
                  <input type="text" defaultValue="de Test" />
              </label>
              <label>
                  Ciutat:
                  <input type="text" defaultValue="Barcelona" />
              </label>
              <div className="button-group" style={{marginTop: '20px'}}>
                  <Link to="/" className="back-btn" style={{textAlign: 'center', textDecoration: 'none', padding: '15px', borderRadius: '10px'}}>Tornar</Link>
                  <button type="submit" className="registre-btn" style={{padding: '15px', borderRadius: '10px'}}>Guardar Canvis</button>
              </div>
          </form>
      </main>
    </>
  );
}

// ------------------------------------
// App Root
// ------------------------------------
export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Discover />} />
        <Route path="/log-in" element={<Login />} />
        <Route path="/messages" element={<MessagesList />} />
        <Route path="/chat/:id" element={<Chat />} />
        <Route path="/profile" element={<Profile />} />
      </Routes>
    </BrowserRouter>
  );
}