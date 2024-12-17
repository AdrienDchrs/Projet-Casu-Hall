/**
 * Fonction en JQuery pour slide tous les éléments de mon carrousel toutes les 4 secondes
 * Si on arrive à la fin du carrousel, on le met en pause
 */
$(document).ready(function() 
{
    carrousel();
});

function carrousel() {
    var $carousel = $('.carousel');

    $carousel.carousel({ 
        interval: 4000
    });

    $carousel.on('slid.bs.carousel', function () 
    {
        var $this = $(this);
        var $items = $this.find('.carousel-item');
        var $activeItem = $this.find('.carousel-item.active');

        if ($items.index($activeItem) === $items.length - 1) {
            $this.carousel('pause');
        }
    });
}


/**
 * Cette fonction permet d'afficher les chaînes de caractères des champs password de la vue. 
 * @param {*} pwd 
 * @param {*} pwd2 
 */
function afficherMotDePasse(pwd, pwd2) 
{
    var seePwd = document.getElementById(pwd);
    var seePwd2 = document.getElementById(pwd2);

    console.log(seePwd + "" + seePwd2)

    if (seePwd.type === "password" || seePwd2.type === "password") 
    {
        seePwd.type = "text";
        seePwd2.type = "text";
    } 
    else 
    {
        seePwd.type = "password";
        seePwd2.type = "password";
    }
}

/**
 * Simplement du JQuery pour afficher le thème modal de Bootstrap qui consiste à voir l'image en plein écran lorsque l'on clique dessus. 
 */
// $(document).ready(function() 
// {
//     $('#zoomImage').on('click', function() 
//     {
//         $('#imageModal').modal('show');
//     });
// });


/**
 * Script permettant d'incrémenter ou de décrémenter le prix total d'un lorsque nous ne sommes pas connectés
 */
function adjustPrice(signe, price, maxQuantity, idArticle) 
{
    var totalOrderPrice = 0;
    var priceElement = document.getElementById("price " + idArticle); 
    var totalPriceElement = document.getElementById("totalPrice"); 

    var operation = document.getElementById(signe).id; 

    var label = document.getElementById("quantity-" + idArticle); 
    var list = document.getElementById("quantityRecap-" + idArticle);
    var productName = list.textContent.split(" x ")[1];

    var currentQuantity = parseInt(label.textContent);
    

    if (operation === "decrease") 
    {
        if (currentQuantity > 1) 
        {
            currentQuantity -= 1; 
        }
    } 
    else if (operation === "increase") 
    {
        if (currentQuantity < maxQuantity) 
        {
            currentQuantity += 1; 
        }
    }

    label.textContent = currentQuantity;
    list.textContent =  currentQuantity + " x " + productName;

    // Calculer le prix du produit
    var calcPrice = price * currentQuantity;
    priceElement.innerText = calcPrice.toFixed(2) + " €";

    totalOrderPrice = 0;
    var allPriceElements = document.querySelectorAll('[id^="price "]');
    
    allPriceElements.forEach(function(priceElement) {
        var priceValue = parseFloat(priceElement.textContent);
        totalOrderPrice += priceValue;
    });

    totalPriceElement.innerText = totalOrderPrice.toFixed(2) + " €";
}

/**
 * Effet de fondu sur les Flash. 
 */
document.addEventListener('DOMContentLoaded', function()
{
    const alerts = document.querySelectorAll('[data-alert]');
    
    alerts.forEach(item => 
    { 
        setTimeout(() => 
        {
            
            item.style.opacity = '1';
            item.style.transition = 'opacity 1s ease-in-out';
            item.style.opacity = '0';
            
            item.addEventListener('transitionend', () => 
            {
                item.parentElement.remove(); 
            });
        }, 1000); 
    });
});

/**
 * Rendre inactif le bouton si les politiques de ventes ne sont pas cochées
 */
function disableButton() 
{
    const checkbox = document.getElementById('flexCheckDefault');
    const orderLink = document.getElementById('orderLink');

    if (!checkbox || !orderLink) 
        return;

    checkbox.addEventListener('change', function () {
        if (this.checked) {
            orderLink.classList.remove('disabled');
            orderLink.removeAttribute('aria-disabled');
        } else {
            orderLink.classList.add('disabled');
            orderLink.setAttribute('aria-disabled', 'true');
        }
    });
}
disableButton();

document.addEventListener('DOMContentLoaded', function () {
    disableButton();
});
