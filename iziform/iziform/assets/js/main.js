// JS simple pour dashboard formateur
document.addEventListener("DOMContentLoaded", function() {
    // Confirmation avant suppression
    const deleteLinks = document.querySelectorAll(".delete-link");
    deleteLinks.forEach(function(link) {
        link.addEventListener("click", function(e) {
            if(!confirm("Voulez-vous vraiment supprimer cet élément ?")){
                e.preventDefault();
            }
        });
    });

    // Simple toggle pour les formulaires si besoin
    const toggleButtons = document.querySelectorAll(".toggle-form");
    toggleButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const target = document.querySelector(btn.dataset.target);
            if(target.style.display === "none" || target.style.display === ""){
                target.style.display = "block";
            } else {
                target.style.display = "none";
            }
        });
    });
});
