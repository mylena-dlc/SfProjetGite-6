
document.addEventListener('DOMContentLoaded', function() {

// Récupérez les éléments HTML
 const numberAdultInput = document.getElementById('numberAdult');
 const numberKidInput = document.getElementById('numberKid');
 const totalPersonInput = document.getElementById('totalPerson');

 // Récupérez les boutons d'incrémentation et de décrémentation
 const incrementAdultButton = document.querySelector('.adult .increment');
 const decrementAdultButton = document.querySelector('.adult .decrement');
 const incrementKidButton = document.querySelector('.kid .increment');
 const decrementKidButton = document.querySelector('.kid .decrement');

  // Vérifiez si les éléments existent avant d'attacher des écouteurs d'événements
  if (numberAdultInput && numberKidInput && totalPersonInput && incrementAdultButton && decrementAdultButton && incrementKidButton && decrementKidButton) {
 // Ajoutez des gestionnaires d'événements pour les boutons
 incrementAdultButton.addEventListener('click', function() {
     incrementInput(numberAdultInput);
 });

 decrementAdultButton.addEventListener('click', function() {
     decrementInput(numberAdultInput);
 });

 incrementKidButton.addEventListener('click', function() {
     incrementInput(numberKidInput);
 });

 decrementKidButton.addEventListener('click', function() {
     decrementInput(numberKidInput);
 });
  }
 // Fonction pour incrémenter l'input et mettre à jour totalPerson
 function incrementInput(inputElement) {
     if (inputElement.value < inputElement.max && getTotalPersons() < 6) {
         inputElement.value++;
         updateTotalPerson();
     }
 }

 // Fonction pour décrémenter l'input et mettre à jour totalPerson
 function decrementInput(inputElement) {
     if (inputElement.value > inputElement.min) {
         inputElement.value--;
         updateTotalPerson();
     }
 }

 // Fonction pour mettre à jour totalPerson en additionnant les valeurs d'adult et kid
 function updateTotalPerson() {
     totalPersonInput.value = Number(numberAdultInput.value) + Number(numberKidInput.value);
 }

 // Fonction pour obtenir la valeur totale des personnes
 function getTotalPersons() {
     return Number(numberAdultInput.value) + Number(numberKidInput.value);
 }


 // Fonction pour valider la soumission du formulaire
 function validateForm() {
    var startDate = document.getElementById('start').value;
    var endDate = document.getElementById('end').value;

    // Vérifier si les dates d'arrivée et de départ sont sélectionnées
    if (!startDate || !endDate) {
        Swal.fire({
            icon: "error",
            title: "Erreur",
            text: "Veuillez sélectionner les dates d'arrivée et de départ."
        });
        return false; // Empêcher la soumission du formulaire si les dates ne sont pas sélectionnées
    }

    return true; // Autoriser la soumission du formulaire si tout est valide
}

});


/* Bouton scroll haut de page */
const btn = document.querySelector('.btn-scroll-to-top');
btn.addEventListener('click', () => {

    window.scrollTo({
        top: 0,
        left: 0,
        behavior: "smooth" // pour adoucir l'effet
    })
})


/* API Leaflet */ 

    // Créez une icône personnalisée avec une couleur différente
    var customIcon = L.icon({
        iconUrl: '../img/icon-localisation.png', 
        // iconUrl: '{{ asset( img/icon-localisation.png ) }}',
        
        iconSize: [38, 38],  // Taille de l'icône en pixels
        iconAnchor: [16, 32],  // Point d'ancrage de l'icône par rapport à son coin inférieur gauche
        popupAnchor: [0, -32],  // Point d'ancrage du popup par rapport à son coin supérieur gauche
    });

    // Initialisez la carte avec l'icône personnalisée
    var map = L.map('map').setView([48.116933, 7.140431], 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 13,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Utilisez l'icône personnalisée pour le marqueur
    var marker = L.marker([48.116933, 7.140431], { icon: customIcon }).addTo(map);
    marker.bindPopup("Le gîte du Rain du Pair").openPopup();











    
        


// Plugin Rate Yo pour l'affichage des étoiles de la notation des avis

$(document).ready(function () {
    $("#rating").rateYo({
        rating: 0, // la valeur initiale
        starWidth: "20px",
        precision: 0, // Désactive les demi-étoiles

        onChange: function (rating, rateYoInstance) {
            // Mettre à jour la valeur du champ caché avec la note sélectionnée
            $("input[name='review[rating]']").val(rating);
        }
    });
});
  




class Carousel {

    /**
     * This callback type is called 'requestCallback' and is displayed as a global symbol.
     * 
     * @callback moveCallback
     * @param {number} index
     */


    /**
     * @param {HTMLElement} getElement
     * @param {object} options
     * @param {object} [options.slidesToScroll=1] Nombre d'éléments à faire défiler
     * @param {object} [options.slidesToVisible=1] Nombre d'éléments visible dans un slide
     * @param {boolean} [options.loop=false] doit-t-on boucler en fin de carousel

     */
    constructor (element, options = {}) {
        this.element = element
        this.options = Object.assign({}, {
            slidesToScroll: 1,
            slidesVisible: 1,
            loop: false
        }, options)

        let children = [].slice.call(element.children)
        this.currentItem = 0
        this.root = this.createDivWithClass('carousel')
        this.container = this.createDivWithClass('carousel__container')
        this.root.appendChild(this.container)
        this.element.appendChild(this.root)
        this.moveCallbacks = []
        this.items = children.map((child) => {
            let item = this.createDivWithClass('carousel__item')
            item.appendChild(child)
            this.container.appendChild(item)
            return item
        })
        this.setStyle()
        this.createNavigation()
        this.moveCallbacks.forEach(cb => cb(0))
    }

/*
Applique les bonnes dimensions aux éléments du carousel
*/

setStyle () {
    let ratio = this.items.length / this.options.slidesVisible
    this.container.style.width = (ratio * 100) + "%"
    this.items.forEach(item => item.style.width = ((100 / this.options.slidesVisible) / ratio) + "%")
}

createNavigation () {
    let nextButton = this.createDivWithClass('carousel__next')
    let prevButton = this.createDivWithClass('carousel__prev')
    this.root.appendChild(nextButton)
    this.root.appendChild(prevButton)
    nextButton.addEventListener('click', this.next.bind(this))
    prevButton.addEventListener('click', this.prev.bind(this))
    if (this.options.loop === true) {
        return
    }
    this.onMove(index => {
        if (index <= 0) {
            prevButton.classList.add('carousel__prev--hidden');
        } else {
            prevButton.classList.remove('carousel__prev--hidden');
        }
        
        if (index >= this.items.length - this.options.slidesVisible) {
            nextButton.classList.add('carousel__next--hidden');
        } else {
            nextButton.classList.remove('carousel__next--hidden');
        }
    })
}

next () {
    this.gotoItem(this.currentItem + this.options.slidesToScroll)

}

prev () {
    this.gotoItem(this.currentItem - this.options.slidesToScroll)
}


/**
 * Déplace le carousel vers l'élément ciblé
 * @param {number} index 
 */
gotoItem (index) {
    if (index < 0 ) {
        index = this.items.length - this.options.slidesVisible
    } else if (index >= this.items.length || (this.items[this.currentItem + this.options.slidesVisible] === undefined && index > this.currentItem)) {
            index = 0
    }
    let translateX = index * -100 / this.items.length
    this.container.style.transform = 'translate3d(' + translateX + '%, 0, 0)'
    this.currentItem = index
    this.moveCallbacks.forEach(cb => cb(index))
}

/**
 * 
 * @param {moveCallback} cb 
 */
onMove(cb) {
    this.moveCallbacks.push(cb)
}

    /**
     * 
     * @param {string} className
     * @returns {HTMLElement} 
     */
    createDivWithClass (className) {
        let div = document.createElement('div')
        div.setAttribute('class', className)
        return div
    }
}

// Carousel pour les avis
document.addEventListener('DOMContentLoaded', function () {
    // Sélectionnez l'élément #carousel
    const carouselElement = document.querySelector('#carousel');

    // Vérifiez si l'élément existe avant de créer le Carousel
    if (carouselElement !== null) {
        new Carousel(carouselElement, {
            slidesVisible: 3,
            slidesToScroll: 1,
            loop: false
        });
    }
});


// Carousel pour les activité
document.addEventListener('DOMContentLoaded', function () {
    // Sélectionnez l'élément #carousel
    const carouselElementActivity = document.querySelector('#map-carousel');

    // Vérifiez si l'élément existe avant de créer le Carousel
    if (carouselElementActivity !== null) {
        new Carousel(carouselElementActivity, {
            slidesVisible: 6,
            slidesToScroll: 3,
            loop: false
        });
    }
});


/**
 * Images modales et carousel dans la galerie
 */

var modalImages = []; // Tableau pour stocker les URL des images
var currentImageIndex = 0; // Index de l'image actuelle

// Fonction pour ouvrir la modal
function openModal(imgSrc) {
    var modal = document.getElementById('myModal');
    var modalImg = document.getElementById('modalImg');

    // Affiche la modal et définit l'URL de l'image à afficher
    modal.style.display = 'block';
    modalImg.src = imgSrc;

    // Stocke les URL des images de la galerie dans le tableau modalImages
    modalImages = document.querySelectorAll('.gallery-item img');
    modalImages = Array.from(modalImages).map(img => img.src);

    // Récupère le bouton de fermeture de la modal
    var closeBtn = document.getElementsByClassName('close')[0];
    closeBtn.onclick = function() {
        closeModal();
    };

    // Définit le gestionnaire d'événements pour le clic en dehors de la modal
    window.onclick = function(event) {
        if (event.target === modal) {
            closeModal();
        }
    };
}

// Fonction pour changer l'image affichée dans la modal
function changeModalImage(direction) {

    // Incrémente ou décrémente l'index de l'image actuelle
    currentImageIndex += direction;
    // Vérifie les limites du tableau
    if (currentImageIndex < 0) {
        currentImageIndex = modalImages.length - 1;
    } else if (currentImageIndex >= modalImages.length) {
        currentImageIndex = 0;
    }

    // Met à jour l'image dans la modal
    var modalImg = document.getElementById('modalImg');
    modalImg.src = modalImages[currentImageIndex];
}

// Fonction pour fermer la modal
function closeModal() {
    var modal = document.getElementById('myModal');
    modal.style.display = 'none';
}






// TIMER LORS D'UNE RÉSERVATION

let searchStartTime = null; // Moment où la recherche commence
let countdownInterval; // Intervalle de temps

// Fonction pour mettre à jour le compte à rebours
function updateCountdown() {

    const countdownElement = document.getElementById('countdown');

    if (countdownElement !== null) {
        const currentTime = new Date().getTime(); // Obtient le temps actuel
        const elapsedTime = currentTime - searchStartTime; // Calcule le temps écoulé depuis le début de la recherche
        // Calcul le temps restant dans le compte à rebours en soustrayant le temps écoulé de 20min (1200secondes)
        const timeLeftInSeconds = Math.max(0, 1200 - Math.floor(elapsedTime / 1000)); 

        // Mise à jour de l'affichage du compte à rebours
        document.getElementById('countdown').innerHTML = `<i class="fa-solid fa-hourglass-half"></i> Il vous reste ${Math.floor(timeLeftInSeconds / 60)}:${(timeLeftInSeconds % 60).toString().padStart(2, '0')} minute(s) pour valider votre réservation`;
        
        // Stocke le compte à rebours en session
        sessionStorage.setItem('countdownTime', timeLeftInSeconds);

        // Si le temps est écoulé, ajout d'un message d'erreur
        if (timeLeftInSeconds === 0) {
            clearInterval(countdownInterval); // Stop l'execution de la fonction
            sessionStorage.clear(); // Efface les données de la session

            // Lorsque le timer se termine, redirection vers la page d'accueil
            Swal.fire({
                icon: "error",
                title: "Erreur",
                text: "Le temps est écoulé. Veuillez recommancer votre réservation.",
                didClose: () => {
                    window.location.href = "/";
                }
            });
        }
    }
}


    // Vérifiez si une recherche est en cours au chargement de la page
        document.addEventListener('DOMContentLoaded', function () {
        searchStartTime = localStorage.getItem('searchStartTime');

        if (searchStartTime !== null) {
            searchStartTime = parseInt(searchStartTime, 10);
            updateCountdown();

            // Lancez un intervalle pour mettre à jour le compte à rebours toutes les secondes
            countdownInterval = setInterval(updateCountdown, 1000);
        }
        });

    // Fonction pour démarrer une nouvelle recherche
    function startNewSearch() {
        searchStartTime = new Date().getTime(); // Enregistre le temps actuel comme le début de recherche
        localStorage.setItem('searchStartTime', searchStartTime); // Stocke le temps en session

        // Lance un intervalle pour mettre à jour le compte à rebours toutes les secondes
        countdownInterval = setInterval(updateCountdown, 1000);

        // Simule le processus de recherche, then assure que le reste ne s'exécutera qu'après la fin de cete simulation
        simulateSearch().then(function () {
            // Arrête l'intervalle après la recherche
            clearInterval(countdownInterval);

            // Réinitiation des valeurs
            searchStartTime = null;
            localStorage.removeItem('searchStartTime');
            document.getElementById('countdown').innerText = '';
        });
    }


        let cookieInsta = getCookie('tarteaucitron')

        function getCookie(name) {
            const cookies = document.cookie.split('; ')
            const value = cookies
                .find(c => c.startsWith(name + "="))
                ?.split('=')[2]
            if (value === undefined) {
                return null
            } 
            return decodeURIComponent(value)
        }

    // Vérifier si les cookies Instagram sont acceptés, afin de ne pas afficher la section "Actualités"
    var instagramSection = document.getElementById('actualites');

    if (cookieInsta == 'true') {
        instagramSection.style.display = 'block';
        console.log('Le consentement aux cookies a été donné');

      
    } 
    if (cookieInsta == 'false') {
        instagramSection.style.display = 'none';
        console.log('Le consentement aux cookies PAS été donné');

    } 



