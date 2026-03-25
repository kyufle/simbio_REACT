import React, { useState } from 'react';
import { Link } from 'react-router-dom';

const Login = () => {
  const [formData, setFormData] = useState({ email: '', password: '' });
  const [error, setError] = useState('');

const LOGIN_API_URL = 'http://localhost:8080/login.php';

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
      window.location.href = '/';
    } else {
      setError(data.message);
    }
  } catch (err) {
    console.error("Detalle del error:", err);
    setError('No se pudo conectar con el servidor. Revisa la consola (F12).');
  }
};

  return (
    <div className="login-page" style={{display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', backgroundColor: '#A3D2CA'}}>
      <main style={{backgroundColor: 'white', borderRadius: '15px', padding: '25px'}}>
        <h1>Iniciar sessió</h1>

        {error && (
          <div className="notification error">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} noValidate style={{display: 'flex', flexDirection: 'column'}}>
          <label style={{width: '100%', display: 'block', marginTop:'20px'}}>
            <p>Correu electrònic</p>
            <input style={{padding: '10px', borderRadius: '5px', width: '100%', display: 'block', marginBottom:'20px'}}
              type="email"
              name="email"
              placeholder="exemple@empresa.cat"
              value={formData.email}
              onChange={handleChange}
              required
            />
          </label>

          <label style={{width: '100%', display: 'block'}}>
            Contrasenya
           <input style={{padding: '10px', borderRadius: '5px', width: '100%', display: 'block', marginBottom:'20px'}}
              type="password"
              name="password"
              placeholder="********"
              value={formData.password}
              onChange={handleChange}
              required
            />
          </label>

          <button type="submit" style={{color: 'white',
    fontWeight: 600,
    background: 'linear-gradient(135deg, #7b68ee, #a3d2ca)',
    padding: '8px 16px',
    borderRadius: '5px',
    cursor: 'pointer'}}>Iniciar sessió</button>
        </form>

        {/* <div style={{ marginTop: '1rem', textAlign: 'center' }}>
          <Link href="/forgot-password" style={{fontSize: '0.9rem', color: '#7B68EE'}}>No recordes la teva contrasenya?</Link>
        </div>

        <div className="button-group">
          <Link to="/register" className="registre-btn" style={{textDecoration: 'none', textAlign: 'center', display: 'flex', alignItems: 'center', justifyContent: 'center'}}>
            Registrar-se
          </Link>
        </div> */}
      </main>
    </div>
  );
};

export default Login;