import React, { useState } from 'react';
import { Link } from 'react-router-dom';

const Login = () => {
  const [formData, setFormData] = useState({ email: '', password: '' });
  const [error, setError] = useState('');

const LOGIN_API_URL = 'http://localhost:8080//simbio_BACKEND/login.php';

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

const handleSubmit = async (e) => {
  e.preventDefault();
  setError('');

  try {
    const response = await fetch(LOGIN_API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include', 
      body: JSON.stringify(formData),
    });

    const data = await response.json();
    
    if (data.success) {
      window.location.href = '/profile';
    } else {
      setError(data.message);
    }
  } catch (err) {
    console.error("Detalle del error:", err);
    setError('No se pudo conectar con el servidor. Revisa la consola (F12).');
  }
};

  return (
    <div className="login-page">
      <main>
        <h1>Iniciar sessió</h1>

        {error && (
          <div className="notification error">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} noValidate>
          <label>
            Correu electrònic
            <input
              type="email"
              name="email"
              placeholder="exemple@empresa.cat"
              value={formData.email}
              onChange={handleChange}
              required
            />
          </label>

          <label>
            Contrasenya
            <input
              type="password"
              name="password"
              placeholder="********"
              value={formData.password}
              onChange={handleChange}
              required
            />
          </label>

          <button type="submit">Iniciar sessió</button>
        </form>

        <div style={{ marginTop: '1rem', textAlign: 'center' }}>
          <Link href="/forgot-password" style={{fontSize: '0.9rem', color: '#7B68EE'}}>No recordes la teva contrasenya?</Link>
        </div>

        <div className="button-group">
          <Link to="/register" className="registre-btn" style={{textDecoration: 'none', textAlign: 'center', display: 'flex', alignItems: 'center', justifyContent: 'center'}}>
            Registrar-se
          </Link>
        </div>
      </main>
    </div>
  );
};

export default Login;