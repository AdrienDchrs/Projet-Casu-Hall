
/**
 * Fonction en JQuery pour slide tous les éléments de mon carrousel toutes les 4 secondes
 * Si on arrive à la fin du carrousel, on le met en pause
 */
$(document).ready(function() {
    carrousel();
});

function carrousel() {
    var $carousel = $('.carousel');

    $carousel.carousel({ interval: 4000 });

    $carousel.on('slid.bs.carousel', function () {
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
 * Cette fonction permet de fixer le footer en cas de contenu inférieur à la hauteur de la fenêtre
 * Cela permet de ne pas avoir le footer en plein milieu de la page
 */
document.addEventListener("DOMContentLoaded", function() 
{
    function footer()
    {
    
        var footer = document.getElementById('footer');
        var body = document.body;
        var html = document.documentElement;
        var height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );

        if(height <= window.innerHeight)
        {
            footer.style.position = 'fixed';
            footer.style.bottom = '0';
            footer.style.width = '100%';
        }
        else
        {
            footer.style.position = 'relative';
        }
    }
    footer();

    window.addEventListener('resize', footer);
});


/**
 * Simplement du JQuery pour afficher le thème modal de Bootstrap
 */
$(document).ready(function() 
{
    $('#zoomImage').on('click', function() 
    {
        $('#imageModal').modal('show');
    });
});

