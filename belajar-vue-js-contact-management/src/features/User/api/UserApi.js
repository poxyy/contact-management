const API = import.meta.env.VITE_API_PATH;

export const userRegister = async ({ username, password, name }) => {
    return await fetch(`${API}/users`, {
        method: 'POST',
        headers: {
            'Content-type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            username,
            password,
            name
        })
    })
}

export const userLogin = async ({ username, password }) => {
    return await fetch(`${API}/users/login`, {
        method: 'POST',
        headers: {
            'Content-type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            username,
            password
        })
    })
}

export const userDetail = async (token) => {
    return await fetch(`${API}/users/current`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    })
}

export const userUpdateName = async (token, { name }) => {
    return await fetch(`${API}/users/current`, {
        method: 'PATCH',
        headers: {
            'Content-type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            name
        })
    })
}

export const userUpdatePassword = async (token, { password }) => {
    return await fetch(`${API}/users/current`, {
        method: 'PATCH',
        headers: {
            'Content-type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            password
        })
    })
}

export const userLogout = async (token) => {
    return await fetch(`${API}/users/logout`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    })
}