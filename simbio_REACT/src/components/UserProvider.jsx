import { createContext, useCallback, useEffect, useState } from 'react';

export const UserContext = createContext({});

export default function UserProvider({ children }) {
    const [user, setUser] = useState();
    const fetchProjects = useCallback(async () => {
        // setLoading(true);
        try {
            const response = await fetch('http://localhost:8080/logged_user.php', {
                credentials: 'include',
            });
            
            const data = await response.json();
            setUser(data.user.name);
            
            // setProjects(sorted);
            // setCurrentIndex(0);
        } catch (err) {
            // setError(err.message);
        } finally {
            // setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchProjects();
    }, [fetchProjects]);

    // useEffect que haga fetch y recupere la informacion del usuario actual
    // guardarlo en un useState
    return (
        <UserContext value={{
            name: user
        }}>
            {children}
        </UserContext>
    );
}