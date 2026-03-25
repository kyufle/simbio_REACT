import React, { useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Link, useNavigate, useParams } from 'react-router-dom';

import './styles.css';
import './chat.css';
import './conversations.css';

import projectsData from './mockData.json';

// Utility for changing body class
function useBodyClass(className) {
  useEffect(() => {
    document.body.className = className;
    return () => { document.body.className = ''; };
  }, [className]);
}

// ------------------------------------
// Header Component (from original)
// ------------------------------------
function Header() {
  return (
    <header>
      <ul className="nav-links">
        <li><Link to="/">Descobrir</Link></li>
        <li><Link to="/profile">Perfil</Link></li>
        <li><Link to="/messages">Converses</Link></li>
      </ul>
      <div className="session-info">
        <a href="https://youtu.be/zSXbPNl1RJw" target="_blank" rel="noreferrer">Video</a>
        |
        <span>Usuario</span>
        <a href="#">Tancar sessió</a>
      </div>
    </header>
  );
}

// ------------------------------------
// Discover Page
// ------------------------------------
function Discover() {
  useBodyClass('discover-page');
  const [projects, setProjects] = useState([]);
  const [animating, setAnimating] = useState(null);
  const [detailsOpen, setDetailsOpen] = useState(false);

  useEffect(() => {
    setProjects(projectsData);
  }, []);

  const currentProject = projects[0];

  const handleAction = (type) => {
    if (!currentProject || animating) return;
    setAnimating(type);
    setTimeout(() => {
      setProjects(prev => prev.slice(1));
      setAnimating(null);
      setDetailsOpen(false);
    }, 500);
  };

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
function MessagesList() {
  useBodyClass('conversations-page');

  return (
    <>
      <div className="sidebar" style={{
        position: 'fixed', top: 0, left: 0, right: 0, zIndex: 1000, 
        background: 'linear-gradient(180deg, rgba(163, 210, 202, 0.95) 0%, rgba(163, 210, 202, 0) 100%)',
        padding: '15px 20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center'
      }}>
        <ul className="nav-links" style={{display: 'flex', gap: '20px', listStyle: 'none'}}>
          <li><Link to="/" style={{fontWeight: 600, color: '#1a1a1a', textDecoration: 'none'}}>Descobrir</Link></li>
          <li><Link to="/profile" style={{fontWeight: 600, color: '#1a1a1a', textDecoration: 'none'}}>Perfil</Link></li>
          <li><Link to="/messages" style={{fontWeight: 600, color: '#1a1a1a', textDecoration: 'none'}}>Converses</Link></li>
        </ul>
        <div className="session-info" style={{fontWeight: 600}}>
          <span>Usuario</span>
        </div>
      </div>

      <main className="conversations-page" style={{paddingTop: '60px'}}>
          <div className="conversations-container">
              <header className="conversations-header">
                  <h1>Converses</h1>
                  <Link to="/" className="btn-back">Enrere</Link>
              </header>

              <div className="conversations-list">
                  <Link to="/chat/21" className="conversation-item unread">
                      <div className="conversation-avatar">
                          <div className="avatar-placeholder">EP</div>
                      </div>
                      <div className="conversation-info">
                          <div className="conversation-header">
                              <h3>Eco-Packaging Textil</h3>
                              <span className="conversation-time">Ara mateix</span>
                          </div>
                          <p className="conversation-entity">Empresa: Inditex</p>
                          <p className="conversation-preview">Hola! Ens encanta el vostre perfil.</p>
                      </div>
                  </Link>

                  <Link to="/chat/32" className="conversation-item">
                      <div className="conversation-avatar">
                          <img src="/uploads/imageproject4.jpg" alt="Avatar Grupo Bimbo" />
                      </div>
                      <div className="conversation-info">
                          <div className="conversation-header">
                              <h3>Fleca Saludable y Celíaca</h3>
                              <span className="conversation-time">Ahir</span>
                          </div>
                          <p className="conversation-entity">Empresa: Grupo Bimbo</p>
                          <p className="conversation-preview">Gràcies pel contacte, parlem aviat.</p>
                      </div>
                  </Link>
              </div>
          </div>
      </main>
    </>
  );
}

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
        <Route path="/messages" element={<MessagesList />} />
        <Route path="/chat/:id" element={<Chat />} />
        <Route path="/profile" element={<Profile />} />
      </Routes>
    </BrowserRouter>
  );
}