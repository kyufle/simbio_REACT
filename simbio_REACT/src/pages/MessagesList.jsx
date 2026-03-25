import { Link } from "react-router-dom";
import useBodyClass from "../hooks/useBodyClass";

export default function MessagesList() {
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