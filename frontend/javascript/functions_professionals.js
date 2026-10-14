import apiFetch  from "./api.js";

document.addEventListener("DOMContentLoaded", ()=>{

    async function loadProfessionals() {
    
        const { response, data } = await apiFetch("professionals", {
            method: "GET"
        });

        console.log(response.status);
        console.log(data);

        if(data && data.length > 0){
            renderProfessionals(data)
        }
    }

    function renderProfessionals(professionals){

        const container = document.getElementById("professionalsContainer");

        professionals.forEach(professional => {

            if(professional){
                const loading = document.querySelector(".loading");
                loading.style.display = "none";

            }
            
            const card= document.createElement("div");
            card.classList.add("professional-card");

            //div de profile 
            profile(card, professional);


            //bio
            bio(card,professional);

            //price
            price(card,professional);

            //button
            profileButton(card, professional);

            container.appendChild(card);

            console.log(professional);
        });

        const div = document.createElement("p");
        div.innerText = data.first_name;

        document.body.append(div);
    }
    loadProfessionals();
});



//function de creación de profile professional
function profile(card,professional) {
    
    const profileHeader = document.createElement("div");
    profileHeader.classList.add("profile-header");

    const profileNome = document.createElement("div");
    profileNome.classList.add("profile-name");
    profileNome.innerText = `${professional.first_name} ${professional.last_name}`;

    const profileService = document.createElement("div");
    profileService.classList.add("profile-service");
    profileService.innerText = `${professional.service}`;

    profileHeader.append(profileNome, profileService);
    card.appendChild(profileHeader);
}

//function de creación de profile BIO

function bio(card,professional){

    const bio = document.createElement("div");
    bio.classList.add("profile-bio");
    bio.innerText = `${professional.bio}`;

    card.appendChild(bio);

}


function price(card,professional){
    const price = document.createElement("div");
    price.classList.add("price");

    if(professional.pricing_type === "fixed"){
        price.innerText = `Valor por serviço a partir de: R$${professional.service_rate}`
    }else{
        price.innerText = `Valor por hora a partir de: R$${professional.hourly_rate}`
    }

    card.appendChild(price);
    
}

async function profileButton(card, professional) {

    const button = document.createElement("a");
    button.classList.add("profile-button");
    button.innerText = "Veja mais";
    button.href = `professional_id.html?id=${professional.id}`;

    card.appendChild(button);

}