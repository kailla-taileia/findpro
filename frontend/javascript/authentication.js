import apiFetch  from "./api.js"
    
async function authentication(){
    const token = localStorage.getItem("token");

    if(!token){
        return false;
    }

    const {response,data} = await apiFetch("auth", {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${token}`
        }
    });

    return response.ok && data.authenticated === true;
}

export default authentication;