import { useCallback, useContext } from "react";
import { UserContext } from "./UserProvider";
import { Link } from "react-router-dom";

export default function Header() {
    const user = useContext(UserContext);
    const logOut = useCallback(async () => {
            // setLoading(true);
            try {
                const response = await fetch('http://localhost:8080/logout.php', {
                    credentials: 'include',
                });
            
            } catch (err) {
                // setError(err.message);
            } finally {
                // setLoading(false);
            }
            window.location.href = '/log-in';
        }, []);
    return (
        <header>
            <ul className="nav-links">
                <li><Link to="/">Descobrir</Link></li>
                <li><Link to="/profile">Perfil</Link></li>
                <li><Link to="/messages">Converses</Link></li>
            </ul>
            <div className="session-info">
                <a href="https://youtu.be/zSXbPNl1RJw" target="_blank" rel="noreferrer">Video</a>
                <span> | </span>{user.name ? <span>{user.name}</span> : null}
                <a href="#" onClick={logOut}>Tancar sessió</a>
            </div>
        </header>
    );
}