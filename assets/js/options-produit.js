const SELECTEUR_RADIO_OPTION = ".choix__radio";
const SELECTEUR_BOUTON_QUANTITE = ".quantite__bouton";
const SELECTEUR_QUANTITE_PANIER = ".woocommerce-cart-form .qty";
const SELECTEUR_MAJ_PANIER = ".woocommerce-cart-form [name='update_cart']";
const DELAI_MAJ_PANIER = 600;

let minuteurPanier;

document.addEventListener("change", (evenement) => {
  const bouton = evenement.target.closest(SELECTEUR_RADIO_OPTION);
  if (!bouton) {
    return;
  }
  const liste = bouton.closest("td").querySelector("select");
  liste.value = bouton.value;
  liste.dispatchEvent(new Event("change", { bubbles: true }));
});

document.addEventListener("click", (evenement) => {
  const bouton = evenement.target.closest(SELECTEUR_BOUTON_QUANTITE);
  if (!bouton) {
    return;
  }
  const champ = bouton.parentElement.querySelector(".qty");
  if (Number(bouton.dataset.pas) > 0) {
    champ.stepUp();
  } else {
    champ.stepDown();
  }
  champ.dispatchEvent(new Event("change", { bubbles: true }));
});

document.addEventListener("change", (evenement) => {
  if (!evenement.target.matches(SELECTEUR_QUANTITE_PANIER)) {
    return;
  }
  clearTimeout(minuteurPanier);
  minuteurPanier = setTimeout(() => {
    const miseAJour = document.querySelector(SELECTEUR_MAJ_PANIER);
    if (miseAJour) {
      miseAJour.disabled = false;
      miseAJour.click();
    }
  }, DELAI_MAJ_PANIER);
});
