document.addEventListener("DOMContentLoaded", function () {

    const prix = document.getElementById("prix");
    const theme = document.getElementById("theme");
    const regime = document.getElementById("regime");
    const personnes = document.getElementById("personnes");

    const menus = document.querySelectorAll(".menu-card");

    function filtrerMenus() {

        const prixMaximum = Number(prix.value);
        const themeChoisi = theme.value;
        const regimeChoisi = regime.value;
        const nombrePersonnes = Number(personnes.value);

        menus.forEach(function (menu) {

            const prixMenu = Number(menu.dataset.prix);
            const themeMenu = menu.dataset.theme;
            const regimeMenu = menu.dataset.regime;
            const personnesMenu = Number(menu.dataset.personnes);

            let afficher = true;

            if (prix.value !== "" && prixMenu > prixMaximum) {
                afficher = false;
            }

            if (themeChoisi !== "" && themeMenu !== themeChoisi) {
                afficher = false;
            }

            if (regimeChoisi !== "" && regimeMenu !== regimeChoisi) {
                afficher = false;
            }

            if (personnes.value !== "" && personnesMenu > nombrePersonnes) {
                afficher = false;
            }

            menu.style.display = afficher ? "block" : "none";
        });
    }

    prix.addEventListener("input", filtrerMenus);
    theme.addEventListener("change", filtrerMenus);
    regime.addEventListener("change", filtrerMenus);
    personnes.addEventListener("input", filtrerMenus);

});