import apiFetch from "./api.js";

document.addEventListener("DOMContentLoaded", ()=>{

    async function ProfessionalProfile() {

        //recuperar o id
        const params = new URLSearchParams(window.location.search);
        const id = params.get("id");


        if(!id){
            console.error("ID do profissional não informado");
            return;
        }
        
        const { response, data } = await apiFetch(
            `professional/${id}`,
            { method: "GET" }
        );

        if(!response.ok){
            console.error("Erro ao buscar profissional:", data);
            return;
        }

        if(data){
            const loading = document.getElementById("loading");
            const errorMessage = document.getElementById("error-message");
            const profile = document.getElementById("professional-profile");

            loading.style.display = "none";
            errorMessage.style.display = "none";
            profile.style.display = "block";

            setFields(data);
        }else{
        }

        setFields(data);

    }

    ProfessionalProfile();
})

function setFields(professional){

    const category = document.getElementById("service-category");
    category.innerText = professional.service;

    const name = document.getElementById("professional-name");
    name.innerText = `${professional.first_name} ${professional.last_name}`;


    //avaliação
    const averageRating = document.getElementById("average-rating");

    if (professional.average_rating != null &&
        Number(professional.average_rating) > 0){

        averageRating.innerText = professional.average_rating;
        
    }


    //contagem de avaliação
    const reviewCount = document.getElementById("review-count");

    if(professional.reviews.length > 0){
        reviewCount.innerText = `(${professional.reviews.length} avaliações)`;
    }


    //biografia
    const bio = document.getElementById("professional-bio");
    bio.innerText = professional.bio;


    //tipo de cobranca 
    const pricingType = document.getElementById("pricing-type");
    const textInfoPrice = document.querySelectorAll(".text-info-price");
    const price = document.querySelectorAll(".service-price");

    
    if(professional.pricing_type === "fixed"){
        pricingType.innerText = "Preço fixo por serviço";

        textInfoPrice.forEach(element => {
            element.innerText = "valor por serviço";
        });

        price.forEach(element => {
            element.innerText = `R$${professional.service_rate}`;
        });
        

    }else{
        pricingType.innerText = "Preço por hora";

        textInfoPrice.forEach(element => {
            element.innerText = "Valor por hora";
        });

        price.forEach(element => {
            element.innerText = `R$${professional.hourly_rate}`;
        });

    }



    //reviews
    const review = document.getElementById("reviews-list");

    if (professional.reviews && professional.reviews.length > 0) {

    const text = document.querySelector(".empty-reviews");
    text.style.display = "none";

    // Limpa as avaliações anteriores
    review.innerHTML = "";

    professional.reviews.forEach(element => {

        // Nota
        const rating = document.createElement("p");
        rating.classList.add("rating");
        rating.innerText = element.rating;

        //Comentário
        const comment = document.createElement("p");
        comment.classList.add("comment");
        comment.innerText = element.comment;

        //Data de criação
        const date = document.createElement("p");
        date.classList.add("create-date");
        date.innerText = element.created_at;

        review.append(rating, comment, date);
    });
}



}