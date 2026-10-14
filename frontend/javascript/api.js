const API_URL = "http://localhost/findpro";

async function apiFetch(route, options = {}) {

    const response = await fetch(`${API_URL}/${route}`, options);

    const data = await response.json();

    return {
        response,
        data
    };
}

export default apiFetch;