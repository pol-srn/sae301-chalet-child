/* Fiche produit variable : quand on coche un bouton (Taille, Cuisson…),
   on recopie la valeur dans la liste déroulante masquée de WooCommerce,
   qui met alors à jour le prix et le stock de la sélection. */
document.addEventListener("change", (evenement) => {
  const bouton = evenement.target.closest(".choix__radio");
  if (!bouton) {
    return;
  }
  const liste = bouton.closest("td").querySelector("select");
  liste.value = bouton.value;
  liste.dispatchEvent(new Event("change", { bubbles: true }));
});

/* Boutons − et + du sélecteur de quantité : on modifie le champ de WooCommerce
   en respectant son minimum, son maximum (stock) et son pas. */
document.addEventListener("click", (evenement) => {
  const bouton = evenement.target.closest(".quantite__bouton");
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
