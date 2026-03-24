import React, { useState } from 'react';

const Register = () => {
  const [formData, setFormData] = useState({
    nombre: '',
    apellidos: '',
    email: '',
    password: '',
    ciudad: '',
    telefono: '',
    entidad: '',
    tipo: ''
  });

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch('http://localhost/tu-proyecto/simbio_BACKEND/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });
      const data = await response.json();
      alert(data.message);
    } catch (error) {
      console.error("Error:", error);
    }
  };

  return (
    <div className="register-container">
      <form onSubmit={handleSubmit}>
        <h1>Registre d'usuari</h1>
        <input type="text" name="nombre" placeholder="Nom" onChange={handleChange} required />
        <input type="text" name="apellidos" placeholder="Cognoms" onChange={handleChange} required />
        <input type="email" name="email" placeholder="Email" onChange={handleChange} required />
        <input type="password" name="password" placeholder="Contrasenya" onChange={handleChange} required />
        <input type="text" name="ciudad" placeholder="Ciutat" onChange={handleChange} required />
        <input type="text" name="telefono" placeholder="Telèfon" onChange={handleChange} required />
        <input type="text" name="entidad" placeholder="Entitat" onChange={handleChange} required />
        <select name="tipo" onChange={handleChange} required>
          <option value="">Selecciona tipus...</option>
          <option value="Empresa">Empresa</option>
          <option value="Centre">Centre</option>
        </select>
        <button type="submit">Registrarse</button>
      </form>
    </div>
  );
};

export default Register;